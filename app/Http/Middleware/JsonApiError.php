<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Ensure errors are formatted correctly for JSON:API.  Right now this only handles 404 errors.
 */
class JsonApiError
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);

            if ($response->getStatusCode() === 404) {
                return $this->toJsonApiResponse('The requested resource could not be found.');
            }

            return $response;

        } catch (NotFoundHttpException $e) {
            return $this->toJsonApiResponse($e->getMessage() ?: 'The requested resource could not be found.');
        }
    }

    /**
     * Format the 404 response to adhere strictly to JSON:API specs.
     */
    protected function toJsonApiResponse(string $detail): Response
    {
        return response()->json([
            'errors' => [
                [
                    'status' => '404',
                    'title' => 'Not Found',
                    'detail' => $detail,
                ]
            ]
        ], 404, [
            'Content-Type' => 'application/vnd.api+json',
        ]);
    }
}
