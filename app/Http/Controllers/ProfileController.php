<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Profil sayfasını gösterir.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Sadece student kullanıcıların kurslarını getir
        $enrolledCourses = $user->courses()
            ->select(
                'courses.id',
                'courses.title',
                'courses.description',
                'courses.instructor',
                'courses.start_date',
                'course_user.created_at as enrolled_at'
            )
            ->get();

        return Inertia::render('Profile/Show', [
            'user' => $user,
            'enrolledCourses' => $enrolledCourses,
        ]);
    }

    /**
     * Profil bilgilerini günceller.
     */
    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();

        $validated = $request->validated();
        $user->update($validated);

        Auth::setUser($user->fresh());

        return back()->with('success', 'Profil bilgileri başarıyla güncellendi ✅');
    }

    /**
     * Şifreyi günceller (sadece harf, rakam, _ ve * karakterleri geçerli).
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^[a-zA-Z0-9_*]+$/', $value)) {
                        $fail('Şifre sadece harf, rakam, _ ve * karakterlerinden oluşabilir ❌');
                    }
                },
            ],
        ], [
            'current_password.required' => 'Mevcut şifre zorunludur.',
            'password.required'         => 'Yeni şifre zorunludur.',
            'password.min'              => 'Yeni şifre en az 8 karakter olmalıdır.',
            'password.confirmed'        => 'Yeni şifreler eşleşmiyor.',
        ]);

        // Mevcut şifre kontrolü
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Mevcut şifreniz yanlış.']);
        }

        // Yeni şifreyi kaydet
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        Auth::setUser($user->fresh());

        return back()->with('success', 'Şifreniz başarıyla güncellendi 🔒');
    }
}
