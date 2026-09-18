<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use LibreNMS\Exceptions\DatabaseConnectException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Ensure errors are formatted correctly for JSON:API.
 */
class FormatJsonApiError
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);

            // Laravel's pipeline may render middleware exceptions before
            // returning control here instead of allowing them to be caught.
            if ($request->routeIs('api.v1.health') && $response->isServerError()) {
                return $this->generateJsonApiErrorResponse(503);
            }

            $errorResponse = $this->generateJsonApiErrorResponse($response->getStatusCode());

            return $errorResponse ?? $response;
        } catch (HttpException $e) {
            $errorResponse = $this->generateJsonApiErrorResponse($e->getStatusCode());

            if ($errorResponse) {
                return $errorResponse;
            }

            throw $e;
        } catch (QueryException $e) {
            if (DatabaseConnectException::upgrade($e) !== null) {
                return $this->generateJsonApiErrorResponse(503);
            }

            throw $e;
        } catch (Throwable $e) {
            if ($request->routeIs('api.v1.health')) {
                return $this->generateJsonApiErrorResponse(503);
            }

            throw $e;
        }
    }

    protected function generateJsonApiErrorResponse(int $code): ?Response
    {
        return match ($code) {
            404 => $this->toJsonApiResponse(404, 'Not Found', 'The requested resource could not be found.'),
            403 => $this->toJsonApiResponse(403, 'Forbidden', 'You do not have the necessary permissions to access this resource.'),
            429 => $this->toJsonApiResponse(429, 'Too Many Requests', 'Too many requests. Please try again later.'),
            503 => $this->toJsonApiResponse(503, 'Service Unavailable', 'A required service is unavailable.'),
            default => null,
        };
    }

    /**
     * Format the response to adhere strictly to JSON:API specs.
     */
    protected function toJsonApiResponse(int $code, string $title, string $detail): Response
    {
        return response()->json([
            'errors' => [
                [
                    'status' => "$code", // JSON:API enforces string here
                    'title' => $title,
                    'detail' => $detail,
                ],
            ],
        ], $code, [
            'Content-Type' => 'application/vnd.api+json',
        ]);
    }
}
