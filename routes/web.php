<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\MyCoursesController;
use App\Http\Controllers\ProfileController;
use App\Models\Course;
use App\Http\Controllers\Student\CourseController as StudentCourseController;

// Ana sayfa
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'appName' => config('app.name'),
        'auth' => [
            'user' => Auth::user()
                ? [
                    'id' => Auth::id(),
                    'name' => Auth::user()->name,
                    'role' => Auth::user()->role,
                ]
                : null,
        ],
    ]);
})->name('home');

// Auth rotaları
require __DIR__ . '/auth.php';

// Profil işlemleri
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});


Route::prefix('student')
    ->middleware(['auth', 'role:student'])
    ->name('student.')
    ->group(function () {
        // Tüm kurslar
        Route::get('courses', [StudentCourseController::class, 'index'])
            ->name('courses.index');

        Route::get('courses/{course}', [StudentCourseController::class, 'show'])
            ->name('courses.show');

        // Kursa kayıt ol
        Route::post('courses/{course}/enroll', [EnrollmentController::class, 'store'])
            ->name('courses.enroll');

        Route::delete('courses/{course}/unenroll', [EnrollmentController::class, 'destroy'])
            ->name('courses.unenroll');

        // Öğrencinin kayıtlı kursları
        Route::get('my-courses', [MyCoursesController::class, 'index'])
            ->name('mycourses.index');

        Route::get('my-courses/{id}', [MyCoursesController::class, 'show'])
            ->name('mycourses.show');
    });


// ADMIN PANELİ
Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->as('admin.')
    ->group(function () {

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Kurs Yönetimi
        Route::get('courses/{course}/students', [CourseController::class, 'students'])
            ->name('courses.students');

        Route::delete('courses/{course}/students/{user}', [CourseController::class, 'removeStudent'])
            ->name('courses.students.remove');

        // Kurs CRUD
        Route::resource('courses', CourseController::class);

        // Ders CRUD
        Route::resource('lessons', LessonController::class)
            ->except(['show'])
            ->parameters(['lessons' => 'lesson']);

        // Öğrenci Yönetimi
        Route::resource('students', StudentController::class)
            ->only(['index', 'show', 'destroy'])
            ->parameters(['students' => 'student']);

        Route::get('api/courses/{course}/lessons', function ($courseId) {
            return \App\Models\Lesson::where('course_id', $courseId)
                ->select('id', 'title', 'content', 'video_url')
                ->orderBy('id', 'desc')
                ->get();
        })->middleware(['auth', 'role:admin']);
    });
