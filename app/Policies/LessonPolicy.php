<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    /**
     * Tüm dersleri listeleme izni
     */
    public function viewAny(?User $user): bool
    {
        // Hem admin hem öğrenci dersleri görebilir
        return $user?->isAdmin() || $user?->isStudent();
    }

    /**
     * Belirli bir dersi görüntüleme izni
     */
    public function view(?User $user, Lesson $lesson): bool
    {
        return $user?->isAdmin() || $user?->isStudent();
    }

    /**
     * Yeni ders oluşturma izni
     */
    public function create(?User $user): bool
    {
        return $user?->isAdmin() ?? false;
    }

    /**
     * Dersi düzenleme izni
     */
    public function update(?User $user, Lesson $lesson): bool
    {
        return $user?->isAdmin() ?? false;
    }

    /**
     * Dersi silme izni
     */
    public function delete(?User $user, Lesson $lesson): bool
    {
        return $user?->isAdmin() ?? false;
    }

    /**
     * Silinen dersi geri yükleme izni
     */
    public function restore(?User $user, Lesson $lesson): bool
    {
        return $user?->isAdmin() ?? false;
    }

    /**
     * Kalıcı silme izni
     */
    public function forceDelete(?User $user, Lesson $lesson): bool
    {
        return $user?->isAdmin() ?? false;
    }
}
