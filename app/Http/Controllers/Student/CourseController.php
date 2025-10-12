<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{


    // Öğrenci tarafında kursları listele (arama + filtreleme)

    public function index(Request $request)
    {
        $query = Course::query()->withCount(['students', 'lessons']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('instructor', 'like', "%{$search}%");
            });
        }

        $sort = $request->get('sort', 'desc');
        $courses = $query->orderBy('created_at', $sort)->paginate(9)->withQueryString();

        // Giriş yapan öğrencinin kayıtlı olduğu kursları al
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $enrolledCourseIds = $user?->courses()
            ->pluck('courses.id')
            ->toArray() ?? [];

        return Inertia::render('Student/Courses/Index', [
            'courses' => $courses,
            'filters' => $request->only(['search', 'sort']),
            'enrolledCourseIds' => $enrolledCourseIds,
        ]);
    }


    // Kurs detay sayfası

    public function show(Course $course, $id)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Kursu bul
        $course = Course::findOrFail($id);

        // İlişkileri tek sorguda yükle
        $course->load(['lessons', 'students']);

        // Öğrenci kursa kayıtlı mı kontrol et
        $enrolled = $user?->courses()
            ->where('course_id', $course->id)
            ->exists() ?? false;

        // Vue sayfasına gönderilecek veri
        return Inertia::render('Student/Courses/Show', [
            'course' => [
                'id'          => $course->id,
                'title'       => $course->title,
                'description' => $course->description,
                'instructor'  => $course->instructor,
                'start_date'  => $course->start_date,

                // Kursa bağlı dersler
                'lessons' => $course->lessons->map(fn($lesson) => [
                    'id'        => $lesson->id,
                    'title'     => $lesson->title,
                    'content'   => $lesson->content,
                    'video_url' => $lesson->video_url,
                ]),

                // Kursa kayıtlı öğrenciler (özet liste)
                'students' => $course->students->map(fn($student) => [
                    'id'        => $student->id,
                    'name'      => $student->name,
                    'email'     => $student->email,
                    'joined_at' => $student->pivot->created_at?->format('d.m.Y H:i'),
                ]),
            ],

            // Vue tarafında buton kontrolü için
            'enrolled' => $enrolled,
        ]);
    }
}
