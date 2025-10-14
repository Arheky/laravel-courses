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
            RateLimiter::hit($this->throttleKey(), self::ATTEMPT_WINDOW_SECONDS);
            if (RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
                event(new Lockout($this));
                $this->applyProgressiveBlock();
            }
            throw ValidationException::withMessages([
                'email' => 'Girdiğiniz e-posta adresi veya şifre hatalı ❌',
            ]);
        }
        RateLimiter::clear($this->throttleKey());
        Cache::forget($this->blockKey());
        Cache::forget($this->offenseKey());
    }
    public function ensureIsNotRateLimited(): void
    {
        if (! Cache::has($this->blockKey())) {
            return;
        }

        $timestamp = (int) Cache::get($this->blockKey());
        $remaining = max($timestamp - now()->timestamp, 1);

        $this->throwRateLimited($remaining);
    }
    protected function applyProgressiveBlock(): never
    {
        $offense = (int) Cache::get($this->offenseKey(), 0) + 1;
        $minutes = (int) min(self::BASE_BLOCK_MINUTES * (2 ** max($offense - 1, 0)), self::MAX_BLOCK_MINUTES);
        $seconds = $minutes * 60;
        Cache::put($this->offenseKey(), $offense, now()->addHours(self::OFFENSE_TTL_HOURS));
        Cache::put($this->blockKey(), now()->addSeconds($seconds)->timestamp, $seconds);
        RateLimiter::clear($this->throttleKey());
        $this->throwRateLimited($seconds);
    }
    protected function throwRateLimited(int $seconds): never
    {
        $message = "Çok fazla hatalı giriş denemesi! {$seconds} saniye bekleyin ⏳";

        $payload = [
            'message'     => $message,
            'retry_after' => $seconds,
            'errors'      => ['email' => [$message]],
        ];

        $e = ValidationException::withMessages($payload['errors']);
        $e->status   = 429;
        $e->response = response()->json($payload, 429);

        throw $e;
    }

    public function throttleKey(): string
    {
        $email = Str::lower((string) $this->input('email'));
        $ip    = $this->ip() ?: request()->getClientIp() ?: 'unknown';

        return Str::transliterate($email . '|' . $ip);
    }

    protected function blockKey(): string
    {
        return 'login_blocked:' . $this->throttleKey();
    }

    protected function offenseKey(): string
    {
        return 'login_offense:' . $this->throttleKey();
    }
}
