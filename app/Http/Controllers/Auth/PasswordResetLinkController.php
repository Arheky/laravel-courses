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


    // Şifre sıfırlama link isteğini işler.

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Rate limiter kontrolü
        $this->ensureIsNotRateLimited($request);

        // Kullanıcı var mı?
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Kayıtlı değilse ceza süresi başlat
            RateLimiter::hit($this->throttleKey($request), $decay = 60);

            throw ValidationException::withMessages([
                'email' => ['Bu e-posta adresi sistemimizde kayıtlı değil.'],
            ]);
        }

        // Mail göndermiyoruz, sadece token üretiyoruz
        $token = app('auth.password.broker')->createToken($user);

        // Şifre sıfırlama bağlantısı (demo amaçlı)
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], false));

        // Laravel'in normal "başarılı gönderim" statüsü
        $status = Password::RESET_LINK_SENT;

        if ($status === Password::RESET_LINK_SENT) {
            // Başarılı istekte rate limiter sıfırlansın
            RateLimiter::clear($this->throttleKey($request));

            // Frontend’e demo linki döndür
            return back()->with([
                'status' => 'Şifre sıfırlama bağlantısı oluşturuldu ✅',
                'demo_reset_link' => $resetUrl,
            ]);
        }

        // Diğer durumlarda deneme sayısını arttır
        RateLimiter::hit($this->throttleKey($request), $decay = 60);

        throw ValidationException::withMessages([
            'email' => ['Şifre sıfırlama bağlantısı oluşturulamadı.'],
        ]);
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
