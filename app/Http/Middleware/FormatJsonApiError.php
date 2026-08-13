<?php

namespace App\Http\Middleware;

use App\Exceptions\JsonApiErrorRenderer;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use LibreNMS\Exceptions\DatabaseConnectException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * Convert failures that never reach JsonApiErrorRenderer::render()
 * (which renders all other v1 API errors) into JSON:API 503 documents:
 * database failures thrown by middleware later in this stack, and server
 * errors that Laravel's pipeline already rendered into the health
 * endpoint's response before returning control here.
 */
class FormatJsonApiError
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);

            if ($request->routeIs('api.v1.health') && $response->isServerError()) {
                return $this->serviceUnavailable();
            }

            return $response;
        } catch (QueryException $e) {
            if (DatabaseConnectException::upgrade($e) !== null) {
                return $this->serviceUnavailable();
            }

            throw $e;
        }
    }

    protected function serviceUnavailable(): Response
    {
        return JsonApiErrorRenderer::render(
            new ServiceUnavailableHttpException(null, 'A required service is unavailable.'),
        );
    }
}
