<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            // Hatalı giriş → Sayaç artır
            RateLimiter::hit($this->throttleKey(), $decaySeconds = 60);

            // Eğer çok fazla deneme varsa backend "cezasını" file cache’e yaz
            $maxAttempts = 5;
            if (RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
                Cache::put(
                    'login_blocked:' . $this->throttleKey(),
                    now()->addSeconds($decaySeconds)->timestamp,
                    $decaySeconds
                );
            }

            throw ValidationException::withMessages([
                'email' => 'Girdiğiniz e-posta adresi veya şifre hatalı ❌',
            ]);
        }

        // Başarılı giriş → sayaç ve cache temizle
        RateLimiter::clear($this->throttleKey());
        Cache::forget('login_blocked:' . $this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        $maxAttempts = 5;
        $decaySeconds = 60;
        $cacheKey = 'login_blocked:' . $this->throttleKey();

        // Eğer kullanıcı zaten file cache cezasında ise
        if (Cache::has($cacheKey)) {
            $timestamp = Cache::get($cacheKey);
            $remaining = max($timestamp - now()->timestamp, 1);

            throw ValidationException::withMessages([
                'email' => "Çok fazla hatalı giriş denemesi! {$remaining} saniye bekleyin ⏳",
            ]);
        }

        // RateLimiter üzerinden klasik kontrol
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        // File cache’e ceza süresini ekle
        Cache::put($cacheKey, now()->addSeconds($seconds)->timestamp, $seconds);

        throw ValidationException::withMessages([
            'email' => "Çok fazla hatalı giriş denemesi! {$seconds} saniye bekleyin ⏳",
        ]);
    }

    public function throttleKey(): string
    {
        // Kullanıcı e-posta + IP bazlı benzersiz kimlik
        return Str::transliterate(Str::lower($this->input('email')) . '|' . $this->ip());
    }
}
