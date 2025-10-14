<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Inertia\Inertia;
use Illuminate\Support\Facades\App;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (TooManyRequestsHttpException $e, $request) {
            $retryAfter = (int) ($e->getHeaders()['Retry-After'] ?? 60);
            $msg = "Çok fazla hatalı giriş denemesi! {$retryAfter} saniye bekleyin ⏳";
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'message'     => $msg,
                    'retry_after' => $retryAfter,
                    'errors'      => ['email' => $msg],
                ], 429);
            }
            return back()->withErrors(['email' => $msg])->withInput();
        });
    }

    public function render($request, Throwable $e)
    {
        // Ortam tespiti
        $isLocal = App::environment('local');

        /**
         * 404 - Model veya sayfa bulunamadı
         */
        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return Inertia::render('Errors/404', [
                'title' => 'Sayfa Bulunamadı',
                'message' => 'Aradığınız sayfa mevcut değil veya kaldırılmış olabilir.',
            ])->toResponse($request)->setStatusCode($isLocal ? 200 : 404);
        }

        /**
         * 403 - Yetkisiz erişim
         */
        if ($e instanceof AuthorizationException ||
            ($e instanceof HttpException && $e->getStatusCode() === 403)) {
            return Inertia::render('Errors/403', [
                'title' => 'Erişim Engellendi',
                'message' => 'Bu sayfaya erişim izniniz bulunmamaktadır.',
            ])->toResponse($request)->setStatusCode($isLocal ? 200 : 403);
        }

        /**
         * 401 - Oturum süresi doldu veya kimlik doğrulaması başarısız
         */
        if ($e instanceof AuthenticationException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Oturumunuz sona erdi, lütfen tekrar giriş yapın.'], 401);
            }

            return Inertia::render('Errors/401', [
                'title' => 'Oturum Süresi Doldu',
                'message' => 'Oturumunuz sona erdi, lütfen tekrar giriş yapın.',
            ])->toResponse($request)->setStatusCode($isLocal ? 200 : 401);
        }

        /**
         * 500 - Sunucu hatası
         */
        if ($e instanceof HttpException && $e->getStatusCode() === 500) {
            return Inertia::render('Errors/500', [
                'title' => 'Bir Hata Oluştu',
                'message' => 'Beklenmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyin.',
            ])->toResponse($request)->setStatusCode($isLocal ? 200 : 500);
        }

        /**
         * Production dışı (local/dev) - Laravel varsayılan debug ekranı
         */
        if ($isLocal) {
            return parent::render($request, $e);
        }

        // Diğer tüm durumlar için genel hata
        return Inertia::render('Errors/500', [
            'title' => 'Bir Hata Oluştu',
            'message' => 'Beklenmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyin.',
        ])->toResponse($request)->setStatusCode($isLocal ? 200 : 500);
    }
}
