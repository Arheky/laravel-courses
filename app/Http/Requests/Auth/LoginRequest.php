<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const MAX_ATTEMPTS_PER_STAGE = 5;
    private const ATTEMPT_DECAY_SECONDS  = 60;k
    private const BASE_COOLDOWN_SECONDS  = 300;
    private const COOLDOWN_CAP_SECONDS   = 3600;
    private const STAGE_TTL_HOURS        = 12;

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
            RateLimiter::hit($this->attemptKey(), self::ATTEMPT_DECAY_SECONDS);
            if (RateLimiter::tooManyAttempts($this->attemptKey(), self::MAX_ATTEMPTS_PER_STAGE)) {
                event(new Lockout($this));
                $stage    = (int) Cache::get($this->stageKey(), 1);
                $cooldown = $this->cooldownForStage($stage);
                Cache::put($this->blockKey(), now()->addSeconds($cooldown)->timestamp, $cooldown);
                Cache::put($this->stageKey(), $stage + 1, now()->addHours(self::STAGE_TTL_HOURS));
                RateLimiter::clear($this->attemptKey());
                $this->throwRateLimited($cooldown);
            }
            throw ValidationException::withMessages([
                'email' => 'Girdiğiniz e-posta adresi veya şifre hatalı ❌',
            ]);
        }
        RateLimiter::clear($this->attemptKey());
        Cache::forget($this->blockKey());
        Cache::forget($this->stageKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (Cache::has($this->blockKey())) {
            $remaining = max(Cache::get($this->blockKey()) - now()->timestamp, 1);
            $this->throwRateLimited($remaining);
        }
    }

    protected function throwRateLimited(int $seconds): void
    {
        $msg = "Çok fazla hatalı giriş denemesi! {$seconds} saniye bekleyin ⏳";
        if ($this->headers->has('X-Inertia') || ! $this->expectsJson()) {
            throw new HttpResponseException(
                back()
                    ->withErrors(['email' => $msg])
                    ->with('retry_after', $seconds)
                    ->setStatusCode(303)
            );
        }
        throw new HttpResponseException(
            response()->json([
                'message'     => $msg,
                'retry_after' => $seconds,
                'errors'      => ['email' => [$msg]],
            ], 429)
        );
    }

    protected function cooldownForStage(int $stage): int
    {
        $seconds = self::BASE_COOLDOWN_SECONDS * (2 ** max(0, $stage - 1));
        return min($seconds, self::COOLDOWN_CAP_SECONDS);
    }
    private function ipKey(): string
    {
        return Str::lower((string) $this->ip());
    }

    private function attemptKey(): string { return 'login_attempts:' . $this->ipKey(); }
    private function blockKey():   string { return 'login_blocked:'  . $this->ipKey(); }
    private function stageKey():   string { return 'login_stage:'    . $this->ipKey(); }
}
