<?php

namespace App\Providers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiExceptionHandlerProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $handler = app('api.exception');
        $handler->register(function (ModelNotFoundException $exception) {
            throw new NotFoundHttpException();
        });
        $handler->register(function (AuthorizationException $exception) {
            throw new AccessDeniedHttpException();
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
