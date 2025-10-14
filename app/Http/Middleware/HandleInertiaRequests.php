<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Inertia'nın root view dosyası.
     */
    protected $rootView = 'app';

    /**
     * Global olarak paylaşılacak veriler (props).
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'demo_reset_link' => fn () => $request->session()->get('demo_reset_link'),
            ],
            'csrf_token' => csrf_token(),
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
            ],
        ]);
    }
}
