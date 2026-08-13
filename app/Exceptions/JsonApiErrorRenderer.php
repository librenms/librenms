<?php

namespace App\Exceptions;

use App\Http\Middleware\EnforceJsonApi;
use Binaryk\LaravelRestify\Exceptions\RepositoryNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Renders exceptions as JSON:API errors-array documents for the v1 API.
 * The single source of truth for the error document shape.
 */
class JsonApiErrorRenderer
{
    public static function render(Throwable $e): JsonResponse
    {
        [$status, $code, $title] = self::classifyException($e);

        if ($e instanceof ValidationException) {
            $entries = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ((array) $messages as $message) {
                    $entries[] = [
                        'status' => (string) $status,
                        'code' => $code,
                        'title' => $title,
                        'detail' => $message,
                        'source' => ['pointer' => '/' . ltrim((string) $field, '/')],
                    ];
                }
            }
            if ($entries === []) {
                $entries[] = self::buildSingleErrorEntry($e, $status, $code, $title);
            }
        } else {
            $entries = [self::buildSingleErrorEntry($e, $status, $code, $title)];
        }

        if ($status >= 500 && config('app.debug')) {
            $entries[0]['meta'] = [
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => self::formatTrace($e),
            ];
        }

        return response()->json(
            ['errors' => $entries],
            $status,
            ['Content-Type' => EnforceJsonApi::CONTENT_TYPE],
        );
    }

    /**
     * @return array{0:int,1:string,2:string} [status, code, title]
     */
    private static function classifyException(Throwable $e): array
    {
        if ($e instanceof ValidationException) {
            return [$e->status, 'validation_failed', 'Validation Failed'];
        }
        if ($e instanceof AuthenticationException) {
            return [401, 'unauthenticated', 'Unauthenticated'];
        }
        if ($e instanceof AuthorizationException) {
            return [403, 'forbidden', 'Forbidden'];
        }
        if ($e instanceof ModelNotFoundException || $e instanceof RepositoryNotFoundException) {
            return [404, 'not_found', 'Not Found'];
        }
        if ($e instanceof ThrottleRequestsException) {
            return [429, 'too_many_requests', 'Too Many Requests'];
        }
        if ($e instanceof HttpExceptionInterface) {
            return self::classifyByStatus($e->getStatusCode());
        }

        return [500, 'server_error', 'Server Error'];
    }

    /**
     * @return array{0:int,1:string,2:string}
     */
    private static function classifyByStatus(int $status): array
    {
        return match ($status) {
            401 => [401, 'unauthenticated', 'Unauthenticated'],
            403 => [403, 'forbidden', 'Forbidden'],
            404 => [404, 'not_found', 'Not Found'],
            405 => [405, 'method_not_allowed', 'Method Not Allowed'],
            429 => [429, 'too_many_requests', 'Too Many Requests'],
            503 => [503, 'service_unavailable', 'Service Unavailable'],
            default => [
                $status,
                'http_' . $status,
                Response::$statusTexts[$status] ?? 'Error',
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function buildSingleErrorEntry(Throwable $e, int $status, string $code, string $title): array
    {
        $detail = $e->getMessage();
        if ($detail === '' || ($status >= 500 && ! config('app.debug'))) {
            $detail = $status >= 500 ? 'An unexpected error occurred.' : $title;
        }

        return [
            'status' => (string) $status,
            'code' => $code,
            'title' => $title,
            'detail' => $detail,
        ];
    }

    /**
     * @return string[]
     */
    private static function formatTrace(Throwable $e): array
    {
        $frames = [];
        foreach (array_slice($e->getTrace(), 0, 10) as $frame) {
            $location = ($frame['file'] ?? '?') . ':' . ($frame['line'] ?? '?');
            $call = ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? '');
            $frames[] = trim($location . ' ' . $call);
        }

        return $frames;
    }
}
