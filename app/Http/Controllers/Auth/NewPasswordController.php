<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    // Şifre sıfırlama formunu gösterir.
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }


    // Şifre sıfırlama isteğini işler.
    public function store(Request $request): RedirectResponse
    {
        // Çok sık sıfırlama isteğine karşı RateLimiter koruması
        $this->ensureIsNotRateLimited($request);

        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults()->min(8),
            ],
        ], [
            'email.exists' => 'Bu e-posta adresiyle kayıtlı bir kullanıcı bulunamadı.',
        ]);

        // Şifre sıfırlama işlemi
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // Başarılı sıfırlama
        if ($status == Password::PASSWORD_RESET) {
            // Başarılı olunca RateLimiter sayacını sıfırla
            RateLimiter::clear($this->throttleKey($request));

            // Rolüne göre yönlendir
            $user = Auth::user();
            if ($user && $user->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('status', 'Şifren başarıyla sıfırlandı 👏');
            }

            if ($user && $user->role === 'student') {
                return redirect()->route('student.courses.index')->with('status', 'Şifren güncellendi, tekrar hoş geldin 👋');
            }

            return redirect()->route('login')->with('status', __('Şifren başarıyla sıfırlandı.'));
        }

        // Başarısız sıfırlama
        RateLimiter::hit($this->throttleKey($request), $decaySeconds = 60);

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }


    // Rate limit kontrolü (çok fazla istek engellenir)

    protected function ensureIsNotRateLimited(Request $request): void
    {
        $maxAttempts = 5;

        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), $maxAttempts)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => ["Çok fazla şifre sıfırlama denemesi yaptınız. {$seconds} saniye bekleyin ⏳"],
        ]);
    }


    // RateLimiter için benzersiz anahtar

    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }
}
