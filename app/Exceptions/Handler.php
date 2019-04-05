<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
    ];

    public function render($request, Exception $exception)
    {
        // If for some reason Blade hasn't been registered, try it now
        try {
            if (!app()->bound('view')) {
                app()->register(\App\Providers\ViewServiceProvider::class);
                app()->register(\Illuminate\Translation\TranslationServiceProvider::class);
            }
        } catch (\Exception $e) {
            // continue without view
        }

        // try to upgrade generic exceptions to more specific ones
        foreach ($this->upgradable as $class) {
            if ($new = $class::upgrade($exception)) {
                return parent::render($request, $new);
            }
        }

        return parent::render($request, $exception);
    }

    /**
     * @param array $convert
     * @return Handler
     */
    public function setConvert(array $convert): Handler
    {
        $this->convert = $convert;
        return $this;
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson() || $request->is('api/*')
            ? response()->json(['message' => $exception->getMessage()], 401)
            : redirect()->guest($exception->redirectTo() ?? route('login'));
    }
}
