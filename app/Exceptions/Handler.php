<?php

namespace App\Exceptions;

use App\Checks;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use LibreNMS\Exceptions\DatabaseConnectException;

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

    public function render($request, Exception $exception)
    {
        // emulate Laravel 5.5 renderable exceptions
        if (method_exists($exception, 'render')) {
            return $exception->render($request);
        }

        return parent::render($request, $exception);
    }

    protected function convertExceptionToResponse(Exception $e)
    {
        // handle database exceptions
        if ($db_response = $this->dbExceptionToResponse($e)) {
            return $db_response;
        }

        // check for exceptions relating to not being able to write to the filesystem
        if ($fs_response = Checks::filePermissionsException($e)) {
            return $fs_response;
        }

        // show helpful response if debugging, otherwise print generic error so we don't leak information
        if (config('app.debug')) {
            return parent::convertExceptionToResponse($e);
        }

        return response()->view('errors.generic', ['exception' => $e]);
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
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }

    protected function dbExceptionToResponse(Exception $e)
    {
        if ($e instanceof QueryException) {
            // connect exception, convert to our standard connection exception
            if (config('app.debug')) {
                // get message form PDO exception, it doesn't contain the query
                $message = $e->getMessage();
            } else {
                $message = $e->getPrevious()->getMessage();
            }

            if (in_array($e->getCode(), [1044, 1045, 2002])) {
                // this Exception has it's own render function
                throw new DatabaseConnectException($message, $e->getCode(), $e);
            }
            return response()->view('errors.generic', [
                'title' => 'Unhandled MySQL Error [' . $e->getCode() . ']',
                'content' => $message
            ]);
        }

        return false;
    }
}
