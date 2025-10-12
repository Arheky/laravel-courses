<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Tüm kursları listeleme izni
     */
    public function viewAny(?User $user): bool
    {
        // Hem admin hem öğrenci kurs listesini görebilir
        return $user?->isAdmin() || $user?->isStudent();
    }

    /**
     * Belirli bir kursu görüntüleme izni
     */
    public function view(?User $user, Course $course): bool
    {
        // Her kayıtlı kullanıcı kurs detaylarını görebilir
        return $user?->isAdmin() || $user?->isStudent();
    }

    /**
     * Yeni kurs oluşturma izni
     */
    public function create(?User $user): bool
    {
        return $user?->isAdmin() ?? false;
    }

    /**
     * Kurs düzenleme izni
     */
    public function update(?User $user, Course $course): bool
    {
        return $user?->isAdmin() ?? false;
    }

    /**
     * Kurs silme izni
     */
    public function delete(?User $user, Course $course): bool
    {
        return $user?->isAdmin() ?? false;
    }

    /**
     * Silinen kursu geri yükleme (isteğe bağlı)
     */
    public function restore(?User $user, Course $course): bool
    {
        return $user?->isAdmin() ?? false;
    }

    /**
     * Kalıcı olarak silme izni
     */
    public function forceDelete(?User $user, Course $course): bool
    {
        return $user?->isAdmin() ?? false;
    }
}
