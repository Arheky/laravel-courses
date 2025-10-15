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
    protected int $maxAttemptsPerStage = 5;
    protected int $baseCooldown = 300;
    protected int $cooldownCap = 3600;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^[a-zA-ZığüşöçİĞÜŞÖÇ0-9\s]+$/u', $value)) {
                        $fail('Ad soyad özel karakter içeremez ⚠️');
                    }
                },
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required', 'confirmed',
                Password::min(8),
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^[a-zA-Z0-9_*]+$/', $value)) {
                        $fail('Şifre sadece harf, rakam, _ ve * karakterlerinden oluşabilir ❌');
                    }
                },
            ],
            'password_confirmation' => ['required', 'string', 'min:8'],
        ];
    }
    public function register(): User
    {
        $this->ensureIsNotRateLimited();

        $user = User::create([
            'name'     => $this->input('name'),
            'email'    => $this->input('email'),
            'password' => Hash::make($this->input('password')),
        ]);
        RateLimiter::clear($this->throttleKey());
        Cache::forget($this->blockKey());
        Cache::forget($this->stageKey());

        return $user;
    }
    protected function failedValidation(Validator $validator)
    {
        RateLimiter::hit($this->throttleKey(), 60);
        parent::failedValidation($validator);
    }
    protected function ensureIsNotRateLimited(): void
    {
        if (Cache::has($this->blockKey())) {
            $remaining = max(Cache::get($this->blockKey()) - now()->timestamp, 1);
            $this->throwTooManyAttempts($remaining);
        }

        $stage = (int) Cache::get($this->stageKey(), 1);

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $this->maxAttemptsPerStage)) {
            return;
        }
        $cooldown = $this->cooldownForStage($stage);

        Cache::put($this->blockKey(), now()->addSeconds($cooldown)->timestamp, $cooldown);

        Cache::put($this->stageKey(), $stage + 1, now()->addHours(12));

        RateLimiter::clear($this->throttleKey());

        $this->throwTooManyAttempts($cooldown);
    }

    protected function throwTooManyAttempts(int $seconds): void
    {
        $msg = "Çok fazla kayıt denemesi! {$seconds} saniye bekleyin ⏳";
        if ($this->headers->has('X-Inertia')) {
            throw new HttpResponseException(
                back()
                    ->withErrors(['email' => $msg])
                    ->with('retry_after', $seconds)
                    ->setStatusCode(303) 
            );
        }
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'retry_after' => $seconds,
                    'message'     => $msg,
                    'errors'      => ['email' => [$msg]],
                ], 429)
            );
        }
        throw ValidationException::withMessages(['email' => $msg]);
    }
    protected function cooldownForStage(int $stage): int
    {
        $seconds = $this->baseCooldown * (2 ** max(0, $stage - 1));
        return min($seconds, $this->cooldownCap);
    }
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
