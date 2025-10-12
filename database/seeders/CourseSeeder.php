<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            [
                'title' => 'Laravel 12 ile Web Geliştirme',
                'description' => 'Laravel 12 kullanarak modern web uygulamaları geliştirmeyi öğrenin.',
                'instructor' => 'Oğuzhan Tekcan',
                'start_date' => now()->addDays(5),
            ],
            [
                'title' => 'Vue.js 3 ve Inertia.js ile Frontend',
                'description' => 'Vue 3 Composition API ve Inertia.js ile Laravel tabanlı SPA geliştirme.',
                'instructor' => 'Elif Öztürk',
                'start_date' => now()->addDays(10),
            ],
            [
                'title' => 'Siber Güvenliğe Giriş',
                'description' => 'Temel siber güvenlik prensiplerini ve web güvenliği yöntemlerini öğrenin.',
                'instructor' => 'Ahmet Şahin',
                'start_date' => now()->addDays(3),
            ],
        ];

        foreach ($courses as $course) {
            Course::updateOrCreate(['title' => $course['title']], $course);
        }
    }
}
