<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Ensure errors are formatted correctly for JSON:API.  Right now this only handles 403 and 404 errors.
 */
class FormatJsonApiError
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);

            $errorResponse = $this->generateJsonApiErrorResponse($response->getStatusCode());

            return $errorResponse ?? $response;

        } catch (HttpException $e) {
            $errorResponse = $this->generateJsonApiErrorResponse($e->getStatusCode());

            if ($errorResponse) {
                return $errorResponse;
            }

            throw $e;
        }
    }

    protected function generateJsonApiErrorResponse(int $code): ?Response
    {
        return match($code) {
            404 => $this->toJsonApiResponse(404, 'Not Found', 'The requested resource could not be found.'),
            403 => $this->toJsonApiResponse(403, 'Forbidden', 'You do not have the necessary permissions to access this resource.'),
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
                ]
            ]
        ], $code, [
            'Content-Type' => 'application/vnd.api+json',
        ]);
    }
}
