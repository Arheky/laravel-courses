<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Ad Soyad (özel karakter yasak)
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^[a-zA-ZığüşöçİĞÜŞÖÇ0-9\s]+$/u', $value)) {
                        $fail('Ad soyad özel karakter içeremez ⚠️');
                    }
                },
            ],

            // 📧 E-posta
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],

            // Şifre
            'password' => [
                'required',
                'confirmed',
                Password::min(8), // sadece min 8 karakter
                function ($attribute, $value, $fail) {
                    // sadece harf, rakam, _ ve * karakterlerine izin ver
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
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'password' => Hash::make($this->input('password')),
        ]);

        RateLimiter::clear($this->throttleKey());
        Cache::forget('register_blocked:' . $this->throttleKey());

        return $user;
    }

    protected function ensureIsNotRateLimited(): void
    {
        $maxAttempts = 5;
        $decaySeconds = 60;
        $cacheKey = 'register_blocked:' . $this->throttleKey();

        if (Cache::has($cacheKey)) {
            $timestamp = Cache::get($cacheKey);
            $remaining = max($timestamp - now()->timestamp, 1);

            throw ValidationException::withMessages([
                'email' => "Çok fazla kayıt denemesi! {$remaining} saniye bekleyin ⏳",
            ]);
        }

        if (!RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());
        Cache::put($cacheKey, now()->addSeconds($seconds)->timestamp, $seconds);

        throw ValidationException::withMessages([
            'email' => "Çok fazla hatalı kayıt denemesi! {$seconds} saniye bekleyin ⏳",
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')) . '|' . $this->ip());
    }
}
