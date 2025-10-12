<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::where('role', 'student');

        // Arama filtresi
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'filters'  => $request->only('search'),
        ]);
    }


    // Belirli bir öğrencinin detaylarını göster (kurslar + dersler)

    public function show($id)
    {
        $student = User::with([
            'courses:id,title,instructor,start_date',
            'courses.lessons:id,course_id,title,video_url',
        ])->findOrFail($id);

        if ($student->role !== 'student') {
            abort(404, 'Bu kullanıcı öğrenci değildir.');
        }

        $this->authorize('view', $student);

        return Inertia::render('Admin/Students/Show', [
            'student' => $student,
        ]);
    }


    // Öğrenciyi sil

    public function destroy(Request $request, $id)
    {
        $student = User::findOrFail($id);

        if ($student->role !== 'student') {
            abort(404, 'Bu kullanıcı öğrenci değildir.');
        }

        $this->authorize('delete', $student);

        $studentId = $student->id;
        $student->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => '🗑️ Öğrenci başarıyla silindi.',
                'id'      => $studentId,
            ]);
        }
        
        return redirect()
            ->route('admin.students.index')
            ->with('success', '🧹 Öğrenci başarıyla silindi.');
    }
}
