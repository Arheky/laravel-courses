<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class EnrollmentController extends Controller
{

    // Öğrencinin kursa kayıt olma işlemi.

    public function store(Course $course, $id)
    {
        /** @var User|null $user */
        $user = Auth::user();
        $course = Course::find($id);

        if (! $user) {
            return back()->with('error', 'Kullanıcı oturumu bulunamadı ❌');
        }

        if ($user->courses()->where('course_id', $course->id)->exists()) {
            return back()->with('error', 'Zaten bu kursa kayıtlısınız ⚠️');
        }

        $user->courses()->attach($course->id, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Kursa başarıyla kayıt olundu 🎉');
    }
    public function destroy(Course $course, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $course = Course::find($id);

        if (! $course) {
            return redirect()
                ->route('student.mycourses.index')
                ->with('error', 'Kurs bulunamadı ⚠️');
        }

        if ($user->courses()->where('course_id', $course->id)->exists()) {
            $user->courses()->detach($course->id);

            // ✅ Kurstan çıkınca "Kurslarım" sayfasına yönlendir
            return redirect()
                ->route('student.mycourses.index')
                ->with('success', 'Kurstan başarıyla çıkıldı ❌');
        }

        return redirect()
            ->route('student.mycourses.index')
            ->with('error', 'Bu kursta kayıt bulunamadı ⚠️');
    }
}
