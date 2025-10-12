<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lesson;
use App\Models\Course;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::all();

        foreach ($courses as $course) {
            for ($i = 1; $i <= 3; $i++) {
                Lesson::updateOrCreate(
                    ['title' => "Ders {$i} - {$course->title}"],
                    [
                        'course_id' => $course->id,
                        'content' => "Bu ders, {$course->title} kursunun {$i}. dersidir.",
                        'video_url' => 'https://www.youtube.com/@freecodecamp',
                    ]
                );
            }
        }
    }
}
