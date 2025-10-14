<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use App\Models\User;

class RegisterRequest extends FormRequest
{
    /** Kaç hatalı denemede bir kademeyi artıracağız */
    protected int $maxAttemptsPerStage = 5;

    /** İlk ceza (saniye) ve katlama katsayısı */
    protected int $baseCooldown = 300; // 5 dk
    protected int $cooldownCap   = 3600; // 60 dk tavan

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Ad Soyad (özel karakter yasak)
            'name' => [
                'required','string','max:255',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^[a-zA-ZığüşöçİĞÜŞÖÇ0-9\s]+$/u', $value)) {
                        $fail('Ad soyad özel karakter içeremez ⚠️');
                    }
                },
            ],

            // E-posta
            'email' => ['required','string','email','max:255','unique:users,email'],

            // Şifre
            'password' => [
                'required','confirmed',
                Password::min(8),
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^[a-zA-Z0-9_*]+$/', $value)) {
                        $fail('Şifre sadece harf, rakam, _ ve * karakterlerinden oluşabilir ❌');
                    }
                },
            ],

            'password_confirmation' => ['required','string','min:8'],
        ];
    }

    /**
     * Kayıt işlemi (başlamadan önce rate limit kontrolü)
     */
    public function register(): User
    {
        $this->ensureIsNotRateLimited();

        $user = User::create([
            'name'     => $this->input('name'),
            'email'    => $this->input('email'),
            'password' => Hash::make($this->input('password')),
        ]);

        // Başarılı olduysa sayaç ve bloklar sıfırlanır
        RateLimiter::clear($this->throttleKey());
        Cache::forget($this->blockKey());
        Cache::forget($this->stageKey());

        return $user;
    }

    /**
     * Validasyon hatası olduğunda deneme sayacını artır (brute-force koruması)
     */
    protected function failedValidation(Validator $validator)
    {
        // 60 saniyelik decay ile attempt artır
        RateLimiter::hit($this->throttleKey(), 60);

        parent::failedValidation($validator);
    }

    /**
     * Kademeli rate limit kontrolü
     */
    protected function ensureIsNotRateLimited(): void
    {
        // Eğer blokta ise direkt 429 dön
        if (Cache::has($this->blockKey())) {
            $remaining = max(Cache::get($this->blockKey()) - now()->timestamp, 1);
            $this->throwTooManyAttempts($remaining);
        }

        // Aşama (stage) al
        $stage = (int) Cache::get($this->stageKey(), 1);

        // Bu aşamadaki 5 denemeyi doldurmadıysa geç
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $this->maxAttemptsPerStage)) {
            return;
        }

        // Aşama doldu → yeni blok süresi hesapla
        $cooldown = $this->cooldownForStage($stage);

        // Blok anahtarını yaz
        Cache::put(
            $this->blockKey(),
            now()->addSeconds($cooldown)->timestamp,
            $cooldown
        );

        // Sonraki aşamaya geç (makul bir TTL ile)
        Cache::put($this->stageKey(), $stage + 1, now()->addHours(12));

        // Bu aşamanın attempt sayacını temizle
        RateLimiter::clear($this->throttleKey());

        // 429
        $this->throwTooManyAttempts($cooldown);
    }

    /**
     * JSON gövdeli 429 fırlat
     */
    protected function throwTooManyAttempts(int $seconds): void
    {
        $payload = [
            'retry_after' => $seconds,
            'message'     => "Çok fazla kayıt denemesi! {$seconds} saniye bekleyin ⏳",
            'errors'      => [
                'email' => ["Çok fazla kayıt denemesi! {$seconds} saniye bekleyin ⏳"],
            ],
        ];

        throw new HttpResponseException(response()->json($payload, 429));
    }

    /**
     * Aşama → ceza süresi (5dk, 10dk, 20dk, 40dk ... 60dk tavan)
     */
    protected function cooldownForStage(int $stage): int
    {
        // stage=1 => 5dk, stage=2 => 10dk, stage=3 => 20dk ...
        $seconds = $this->baseCooldown * (2 ** max(0, $stage - 1));
        return min($seconds, $this->cooldownCap);
    }

    /**
     * Rate limiter anahtarı
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')) . '|' . $this->ip());
    }

    protected function blockKey(): string
    {
        return 'register_blocked:' . $this->throttleKey();
    }

    protected function stageKey(): string
    {
        return 'register_stage:' . $this->throttleKey();
    }
}
