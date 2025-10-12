<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Bu URL’ler CSRF kontrolünden hariç tutulur.
     */
    protected $except = [
        'sanctum/csrf-cookie',
        'login',
        'logout',
    ];
}
