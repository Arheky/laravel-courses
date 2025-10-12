<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class StudentUserSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            ['name' => 'Ahmet Yılmaz', 'email' => 'ahmet@example.com'],
            ['name' => 'Ayşe Demir', 'email' => 'ayse@example.com'],
            ['name' => 'Mehmet Kara', 'email' => 'mehmet@example.com'],
            ['name' => 'Zeynep Arslan', 'email' => 'zeynep@example.com'],
        ];

        foreach ($students as $student) {
            User::updateOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'password' => Hash::make('password'),
                    'role' => 'student',
                ]
            );
        }
    }
}
