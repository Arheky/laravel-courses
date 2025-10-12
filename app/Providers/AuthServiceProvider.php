<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Policies\CoursePolicy;
use App\Policies\DashboardPolicy;
use App\Policies\LessonPolicy;
use App\Policies\StudentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Model → Policy eşlemeleri
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Course::class => CoursePolicy::class,
        Lesson::class => LessonPolicy::class,
        User::class => StudentPolicy::class,
        'dashboard' => DashboardPolicy::class,
    ];

    /**
     * Yetkilendirme servislerinin tanımlandığı metod
     */
    public function boot(): void
    {
        $this->registerPolicies();

        /**
         * 1. Admin için global izin (403 engelleyici)
         * — Eğer kullanıcı admin ise, bütün Policy kontrollerini otomatik geçer.
         */
        Gate::before(function ($user, $ability) {
            if ($user?->isAdmin()) {
                return true;
            }
        });

        /**
         * 2. manage-content izni (bazı Gate::authorize çağrılarında kullanılıyor)
         * — Örneğin DashboardController ve diğer admin-only alanlar için.
         */
        Gate::define('manage-content', function (User $user) {
            return $user->isAdmin();
        });
    }
}
