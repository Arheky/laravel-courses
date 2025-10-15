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
    private const MAX_ATTEMPTS = 5;
    private const ATTEMPT_WINDOW_SECONDS = 300;
    private const BASE_BLOCK_MINUTES = 5;
    private const MAX_BLOCK_MINUTES = 60;
    private const OFFENSE_TTL_HOURS = 12;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
    
            $decaySeconds = $this->decaySeconds();
            RateLimiter::hit($this->throttleKey(), $decaySeconds);

            if (RateLimiter::tooManyAttempts($this->throttleKey(), $this->maxAttempts())) {
                $seconds = RateLimiter::availableIn($this->throttleKey());
                Cache::put('login_blocked:'.$this->throttleKey(), now()->addSeconds($seconds)->timestamp, $seconds);
                $this->throwRateLimited($seconds);
            }

            throw ValidationException::withMessages([
                'email' => __('Girdiğiniz e-posta adresi veya şifre hatalı ❌'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Cache::forget('login_blocked:'.$this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        $cacheKey = 'login_blocked:'.$this->throttleKey();
        if (Cache::has($cacheKey)) {
            $remaining = max(Cache::get($cacheKey) - now()->timestamp, 1);
            $this->throwRateLimited($remaining);
        }

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $this->maxAttempts())) {
            return;
        }

        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());
        Cache::put($cacheKey, now()->addSeconds($seconds)->timestamp, $seconds);

        $this->throwRateLimited($seconds);
    }

    protected function throwRateLimited(int $seconds): void
    {
        $msg = "Çok fazla hatalı giriş denemesi! {$seconds} saniye bekleyin ⏳";
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
                    'message'     => $msg,
                    'retry_after' => $seconds,
                    'errors'      => ['email' => [$msg]],
                ], 429)
            );
        }
        throw ValidationException::withMessages(['email' => $msg]);
    }
    protected function maxAttempts(): int { return 5; }
    protected function decaySeconds(): int { return 60; }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}
