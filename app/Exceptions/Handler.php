<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * A list of the exceptions that can be upgraded. Checked in order.
     *
     * @var array
     */
    protected $upgradable = [
        \LibreNMS\Exceptions\FilePermissionsException::class,
        \LibreNMS\Exceptions\DatabaseConnectException::class,
        \LibreNMS\Exceptions\DuskUnsafeException::class,
        \LibreNMS\Exceptions\UnserializableRouteCache::class,
        \LibreNMS\Exceptions\MaximumExecutionTimeExceeded::class,
        \LibreNMS\Exceptions\DatabaseInconsistentException::class,
    ];

    public function render($request, Throwable $exception)
    {
        // If for some reason Blade hasn't been registered, try it now
        try {
            if (! app()->bound('view')) {
                app()->register(\Illuminate\View\ViewServiceProvider::class);
                app()->register(\Illuminate\Translation\TranslationServiceProvider::class);
            }
        } catch (\Exception $e) {
            // continue without view
        }

        // try to upgrade generic exceptions to more specific ones
        if (! config('app.debug')) {
            foreach ($this->upgradable as $class) {
                if ($new = $class::upgrade($exception)) {
                    return parent::render($request, $new);
                }
            }
        }

        return parent::render($request, $exception);
    }

    protected function convertExceptionToArray(Throwable $e)
    {
        // override the non-debug error output to clue in user on how to debug
        if (! config('app.debug') && ! $this->isHttpException($e)) {
            return ['message' => 'Server Error: Set APP_DEBUG=true to see details.'];
        }

        return parent::convertExceptionToArray($e);
    }
}
