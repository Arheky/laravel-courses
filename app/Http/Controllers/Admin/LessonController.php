<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Models\Lesson;
use App\Models\Course;
use Inertia\Inertia;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Lesson::class);

        $query = Lesson::with('course:id,title');

        // Arama filtresi (ders veya kurs adına göre)
        if ($raw = $request->string('search')->toString()) {
            $normalized = Str::of($raw)
                ->lower()
                ->replaceMatches('/[^\pL\pN\s]+/u', ' ')
                ->squish();
            $terms = collect(explode(' ', $normalized))->filter();
            if ($terms->isNotEmpty()) {
                $driver = DB::getDriverName();
                $like = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';
                $query->where(function ($outer) use ($terms, $like) {
                    foreach ($terms as $term) {
                        $pattern = "%{$term}%";
                        $outer->orWhere(function ($q) use ($pattern, $like) {
                            $q->where('title',   $like, $pattern)
                              ->orWhere('content', $like, $pattern)
                              ->orWhereHas('course', function ($cq) use ($pattern, $like) {
                                  $cq->where('title', $like, $pattern)
                                     ->orWhere('instructor', $like, $pattern);
                              });
                        });
                    }
                });
            }
        }

        $lessons = $query->orderByDesc('created_at')->paginate(6)->withQueryString();
        $courses = Course::select('id', 'title')->orderBy('title')->get();

        return Inertia::render('Admin/Lessons/Index', [
            'lessons' => $lessons,
            'courses' => $courses,
            'filters' => $request->only('search'),
        ]);
    }


    //Yeni ders oluşturma formu

    public function create()
    {
        $this->authorize('create', Lesson::class);

        $courses = Course::select('id', 'title')->orderBy('title')->get();

        return Inertia::render('Admin/Lessons/Form', [
            'courses' => $courses,
        ]);
    }


    // Yeni ders kaydet

    public function store(StoreLessonRequest $request)
    {
        $this->authorize('create', Lesson::class);

        Lesson::create($request->validated());

        return redirect()
            ->route('admin.lessons.index')
            ->with('success', '✅ Ders başarıyla oluşturuldu.');
    }


    //Belirli bir dersi detaylı görüntüle

    public function show($id)
    {
        $lesson = Lesson::with('course:id,title,instructor,start_date')->findOrFail($id);
        $this->authorize('view', $lesson);

        return Inertia::render('Admin/Lessons/Show', [
            'lesson' => $lesson,
        ]);
    }


    // Ders düzenleme formu

    public function edit($id)
    {
        $lesson = Lesson::with('course:id,title')->findOrFail($id);
        $this->authorize('update', $lesson);

        $courses = Course::select('id', 'title')->orderBy('title')->get();

        return Inertia::render('Admin/Lessons/Form', [
            'lesson'  => $lesson,
            'courses' => $courses,
        ]);
    }


    // Dersi güncelle

    public function update(UpdateLessonRequest $request, $id)
    {
        $lesson = Lesson::findOrFail($id);
        $this->authorize('update', $lesson);

        $lesson->update($request->validated());

        return redirect()
            ->route('admin.lessons.index')
            ->with('success', '✅ Ders başarıyla güncellendi.');
    }


    // Dersi sil

    public function destroy($id)
    {
        $lesson = Lesson::findOrFail($id);
        $this->authorize('delete', $lesson);

        $lesson->delete();

        return redirect()
            ->route('admin.lessons.index')
            ->with('success', '🗑️ Ders başarıyla silindi.');
    }
}
