<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
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
        $request->validate([
            'email' => ['required', 'email'],
        ]);
        $genericStatus = '📩 Eğer e-posta sistemimizde varsa, sıfırlama bağlantısı gönderildi.';

        try {
            Password::sendResetLink($request->only('email'));
            if (config('app.debug') || (bool) env('SHOW_RESET_LINK_IN_UI', false)) {
                $user = User::where('email', $request->input('email'))->first();
                if ($user) {
                    $token = Password::broker()->createToken($user);
                    $email = $user->getEmailForPasswordReset();
                    $link = url(route('password.reset', ['token' => $token, 'email' => $email], false));
                    session()->flash('demo_reset_link', $link);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Forgot password error', ['error' => $e->getMessage()]);
        }
        return back()->with('status', $genericStatus);
    }
}
