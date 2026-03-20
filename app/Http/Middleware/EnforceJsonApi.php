<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceJsonApi
{
    public const CONTENT_TYPE = 'application/vnd.api+json';

    public function handle(Request $request, Closure $next): Response
    {
        // Reject request bodies with wrong Content-Type (JSON:API §5.3)
        if ($request->getContent() && ! $this->isJsonApi($request->header('Content-Type', ''))) {
            return response()->json([
                'errors' => [['status' => '415', 'title' => 'Unsupported Media Type']],
            ], 415)->withHeaders(['Content-Type' => self::CONTENT_TYPE]);
        }

        // Tell Laravel to treat this as a JSON request
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // Set JSON:API Content-Type on responses (§5.2)
        $response->headers->set('Content-Type', self::CONTENT_TYPE);

        return $response;
    }

    private function isJsonApi(string $contentType): bool
    {
        // Accept both application/vnd.api+json and application/json for pragmatism
        return str_contains($contentType, 'application/vnd.api+json')
            || str_contains($contentType, 'application/json');
    }
}
