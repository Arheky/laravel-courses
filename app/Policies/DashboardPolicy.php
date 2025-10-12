<?php

namespace App\Policies;

use App\Models\User;

class DashboardPolicy
{
    /**
     * Admin dashboard'a erişim izni
     */
    public function view(User $user): bool
    {
        return $user->isAdmin();
    }
}
