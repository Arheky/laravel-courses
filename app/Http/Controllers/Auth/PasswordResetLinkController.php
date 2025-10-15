<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{

    // Şifre sıfırlama formunu gösterir.

    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
            'demo_reset_link' => session('demo_reset_link'),
        ]);
    }


    public function store(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);
        $status = Password::sendResetLink($request->only('email'));

        $demoLink = null;
        if (config('mail.default') === 'log' || (bool) env('SHOW_DEMO_RESET_LINK', false)) {
            if ($user = User::where('email', $request->email)->first()) {
                $token = app('auth.password.broker')->createToken($user);
                if (config('app.env') !== 'local') {
                    URL::forceScheme('https');
                }

                $demoLink = route('password.reset', [
                    'token' => $token,
                    'email' => $user->email,
                ]);
            }
        }

        if ($status === Password::RESET_LINK_SENT) {
            return back()
                ->with('status', __($status))
                ->with('demo_reset_link', $demoLink);
        }

        return back()->withErrors(['email' => __($status)]);
    }
}


    // RateLimiter kontrolü (max 5 deneme)

    protected function ensureIsNotRateLimited(Request $request): void
    {
        $maxAttempts = 5;

        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), $maxAttempts)) {
            return;
        }

        // Limit aşıldı → kilitle
        event(new Lockout($request));
        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => ["Çok fazla istek gönderdiniz! Lütfen {$seconds} saniye bekleyin ⏳"],
        ]);
    }


    // Kullanıcı + IP bazlı RateLimiter anahtarı

    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }
}
