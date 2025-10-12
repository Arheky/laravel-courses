<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardRequest;
use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index(DashboardRequest $request)
    {
        // Yalnızca admin erişebilir (Policy)
        Gate::authorize('manage-content');

        // Genel istatistikler
        $stats = [
            'totalCourses'      => Course::count(),
            'totalStudents'     => User::where('role', 'student')->count(),
            'totalEnrollments'  => DB::table('course_user')->count(),
            'todayEnrollments'  => DB::table('course_user')
                                        ->whereDate('created_at', today())
                                        ->count(),
            'latestLessons'     => Lesson::whereDate('created_at', '>=', now()->subDays(7))->count(),
            'latestStudents'    => User::where('role', 'student')
                                       ->whereDate('created_at', '>=', now()->subDays(7))
                                       ->count(),
        ];

        // Son 5 kayıt (kullanıcı + kurs bilgisiyle)
        $latestEnrollments = DB::table('course_user')
            ->join('users', 'course_user.user_id', '=', 'users.id')
            ->join('courses', 'course_user.course_id', '=', 'courses.id')
            ->select([
                'course_user.id',
                'users.name as user_name',
                'users.email as user_email',
                'courses.title as course_title',
                'course_user.created_at as enrolled_at',
            ])
            ->latest('course_user.created_at')
            ->limit(5)
            ->get()
            ->map(fn($record) => [
                'id'           => $record->id,
                'user_name'    => $record->user_name,
                'user_email'   => $record->user_email,
                'course_title' => $record->course_title,
                'enrolled_at'  => $record->enrolled_at
                    ? date('d M Y, H:i', strtotime($record->enrolled_at))
                    : '-',
            ]);

        // En popüler 5 kurs (öğrenci sayısına göre)
        $popularCourses = Course::withCount('students')
            ->orderByDesc('students_count')
            ->limit(5)
            ->get(['id', 'title'])
            ->map(fn($course) => [
                'id'          => $course->id,
                'title'       => $course->title,
                'enrollments' => $course->students_count,
            ]);

        // Son eklenen 5 kurs
        $latestCourses = Course::latest()
            ->limit(5)
            ->get(['id', 'title', 'instructor', 'created_at'])
            ->map(fn($course) => [
                'id'         => $course->id,
                'title'      => $course->title,
                'instructor' => $course->instructor,
                'created_at' => $course->created_at
                    ? $course->created_at->format('d M Y, H:i')
                    : '-',
            ]);

        // Dashboard sayfası (Inertia)
        return Inertia::render('Admin/Dashboard', [
            'stats'             => $stats,
            'latestEnrollments' => $latestEnrollments,
            'popularCourses'    => $popularCourses,
            'latestCourses'     => $latestCourses,
        ]);
    }
}
