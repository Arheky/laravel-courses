<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = $request->register();

        // Varsayılan rolü belirle
        $user->role = 'student';
        $user->save();

        event(new Registered($user));

        return redirect()
            ->route('login')
            ->with('status', '🎉 Kayıt başarılı! Şimdi giriş yaparak devam edebilirsiniz.');
    }
}
