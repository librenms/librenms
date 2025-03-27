<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
        \Symfony\Component\Console\Exception\CommandNotFoundException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
            if ($exception instanceof \Illuminate\View\ViewException || $exception instanceof \Spatie\LaravelIgnition\Exceptions\ViewException) {
                $base = $exception->getPrevious(); // get real exception
            }

            foreach ($this->upgradable as $class) {
                if ($new = $class::upgrade($base ?? $exception)) {
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
