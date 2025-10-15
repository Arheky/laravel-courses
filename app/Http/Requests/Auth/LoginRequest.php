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
    private const DECAY_SECONDS = 60;

    private const BASE_BLOCK_MIN   = 5;
    private const MAX_BLOCK_MIN    = 60;
    private const OFFENSE_TTL_HOURS= 12;

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email'    => ['required','string','email'],
            'password' => ['required','string'],
            'remember' => ['sometimes','boolean'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email','password'), $this->boolean('remember'))) {

            RateLimiter::hit($this->throttleKey(), self::DECAY_SECONDS);

            if (RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
                $offense  = (int) Cache::get($this->offenseKey(), 0);
                $cooldown = $this->cooldownForOffense($offense);

                Cache::put($this->blockKey(), now()->addSeconds($cooldown)->timestamp, $cooldown);
                Cache::put($this->offenseKey(), $offense + 1, now()->addHours(self::OFFENSE_TTL_HOURS));

                RateLimiter::clear($this->throttleKey());

                $this->throwRateLimited($cooldown);
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
        if (Cache::has($this->blockKey())) {
            $remaining = max(Cache::get($this->blockKey()) - now()->timestamp, 1);
            $this->throwRateLimited($remaining);
        }

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $offense  = (int) Cache::get($this->offenseKey(), 0);
        $cooldown = $this->cooldownForOffense($offense);

        Cache::put($this->blockKey(), now()->addSeconds($cooldown)->timestamp, $cooldown);
        Cache::put($this->offenseKey(), $offense + 1, now()->addHours(self::OFFENSE_TTL_HOURS));
        RateLimiter::clear($this->throttleKey());

        $this->throwRateLimited($cooldown);
    }

    protected function throwRateLimited(int $seconds): void
    {
        $msg = "Çok fazla hatalı giriş denemesi! {$seconds} saniye bekleyin ⏳";
        session()->flash('retry_after', $seconds);

        $e = ValidationException::withMessages(['email' => $msg]);
        $e->status = 429;
        throw $e;
    }

    protected function cooldownForOffense(int $offense): int
    {
        $minutes = min(self::BASE_BLOCK_MIN * (2 ** max(0, $offense)), self::MAX_BLOCK_MIN);
        return $minutes * 60;
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')) . '|' . $this->ip());
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
