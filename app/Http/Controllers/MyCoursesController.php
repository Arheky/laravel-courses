<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MyCoursesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            abort(403, 'Bu sayfayı görmek için giriş yapmalısınız.');
        }
    
        $query = $user->courses()
            ->select('courses.*')
            ->withCount(['lessons', 'students']);
        $sort = in_array($request->query('sort'), ['asc','desc'], true)
            ? $request->query('sort')
            : 'desc';
    
        $orderCol = method_exists($query, 'qualifyColumn')
            ? $query->qualifyColumn('created_at')
            : (new \App\Models\Course)->getTable().'.created_at';
    
        $courses = $query
            ->orderBy($orderCol, $sort)
            ->paginate(9)
            ->withQueryString();
        return Inertia::render('Student/MyCourses/Index', [
            'courses' => $courses,
            'filters' => $request->only(['search', 'sort']),
        ]);
    }

    public function show(Course $course, $id)
    {
        $course = Course::find($id);
        // İlişkileri tek sorguda yükle
        $course->load(['lessons', 'students']);

        return Inertia::render('Student/MyCourses/Show', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'instructor' => $course->instructor,
                'start_date' => $course->start_date,

                // Kursa bağlı dersler
                'lessons' => $course->lessons->map(fn($lesson) => [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'content' => $lesson->content,
                    'video_url' => $lesson->video_url,
                ]),

                // Kursa kayıtlı öğrenciler
                'students' => $course->students->map(fn($student) => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'joined_at' => $student->pivot->created_at?->format('d.m.Y H:i'),
                ]),
            ],
        ]);
    }

    public function destroy(Course $course)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */
        if (! $user->courses()->where('course_id', $course->id)->exists()) {
            return back()->with('error', 'Bu kursa kayıtlı değilsiniz ⚠️');
        }

        $user->courses()->detach($course->id);

        return back()->with('success', 'Kurs kaydınız başarıyla kaldırıldı 🧹');
    }
}
