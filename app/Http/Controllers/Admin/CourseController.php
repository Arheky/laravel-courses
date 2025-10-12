<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class CourseController extends Controller
{

     //  Kursları listele (arama + sayfalama)
    public function index(Request $request)
    {
        $this->authorize('viewAny', Course::class);

        $query = Course::withCount(['students', 'lessons']);

        // Arama filtresi
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('instructor', 'like', "%{$search}%");
            });
        }

        $courses = $query->latest()->paginate(6)->withQueryString();

        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses,
            'filters' => $request->only('search'),
        ]);
    }

     // Yeni kurs oluşturma formu
    public function create()
    {
        $this->authorize('create', Course::class);

        return Inertia::render('Admin/Courses/Form');
    }

     //Yeni kurs kaydet
    public function store(StoreCourseRequest $request)
    {
        $this->authorize('create', Course::class);

        Course::create($request->validated());

        return redirect()
            ->route('admin.courses.index')
            ->with('success', '✅ Kurs başarıyla oluşturuldu.');
    }

    //Kurs düzenleme formu

    public function edit($id)
    {
        // Manuel binding → güvenli ve Policy uyumlu
        $course = Course::findOrFail($id);
        $this->authorize('update', $course);

        return Inertia::render('Admin/Courses/Form', [
            'course' => $course,
        ]);
    }


    //Kursu güncelle

    public function update(UpdateCourseRequest $request, $id)
    {
        $course = Course::findOrFail($id);
        $this->authorize('update', $course);

        $course->update($request->validated());

        return redirect()
            ->route('admin.courses.index')
            ->with('success', '✅ Kurs başarıyla güncellendi.');
    }


    //Kursu sil

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $this->authorize('delete', $course);

        //İlişkileri temizle, kursu sil
        $course->students()->detach();
        $course->lessons()->delete();
        $course->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('success', '🗑️ Kurs başarıyla silindi.');
    }


    // Kurs detay sayfası

    public function show($id)
    {
        $course = Course::with(['lessons', 'students'])->findOrFail($id);
        $this->authorize('view', $course);

        return Inertia::render('Admin/Courses/Show', [
            'course' => [
                'id'          => $course->id,
                'title'       => $course->title,
                'description' => $course->description,
                'instructor'  => $course->instructor,
                'start_date'  => $course->start_date,
                'lessons'     => $course->lessons->map(fn($lesson) => [
                    'id'        => $lesson->id,
                    'title'     => $lesson->title,
                    'content'   => $lesson->content,
                    'video_url' => $lesson->video_url,
                ]),
                'students'    => $course->students->map(fn($student) => [
                    'id'        => $student->id,
                    'name'      => $student->name,
                    'email'     => $student->email,
                    'joined_at' => $student->pivot->created_at?->format('d.m.Y H:i'),
                ]),
            ],
        ]);
    }


     //Kursa kayıtlı öğrenciler

    public function students($id)
    {
        $course = Course::findOrFail($id);
        $this->authorize('view', $course);

        $students = $course->students()
            ->orderBy('users.name')
            ->paginate(9)
            ->withQueryString();

        return Inertia::render('Admin/Courses/Students', [
            'course'   => $course->only(['id', 'title']),
            'students' => $students,
        ]);
    }


    // Öğrenciyi kurstan kaldır

    public function removeStudent($courseId, User $user)
    {
        $course = Course::findOrFail($courseId);
        $this->authorize('update', $course);

        $course->students()->detach($user->id);

        return back()->with('success', '🧹 Öğrenci başarıyla kurstan çıkarıldı.');
    }
}
