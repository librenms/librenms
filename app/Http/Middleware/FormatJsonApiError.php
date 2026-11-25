<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use LibreNMS\Exceptions\DatabaseConnectException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Ensure errors are formatted correctly for JSON:API.
 */
class FormatJsonApiError
{
    /**
     * Headers a client needs to act on an error, kept when the body is replaced.
     */
    private const array PRESERVED_HEADERS = ['Allow', 'Retry-After', 'WWW-Authenticate', 'X-RateLimit-Limit', 'X-RateLimit-Remaining', 'X-RateLimit-Reset'];

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);

            // Laravel's pipeline may render middleware exceptions before
            // returning control here instead of allowing them to be caught.
            if ($request->routeIs('api.v1.health') && $response->isServerError()) {
                return $this->generateJsonApiErrorResponse(503);
            }

            return $this->formatErrorResponse($response);
        } catch (HttpException $e) {
            $errorResponse = $this->generateJsonApiErrorResponse($e->getStatusCode(), $e->getMessage());

            if ($errorResponse) {
                $errorResponse->headers->add($e->getHeaders());

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

    /**
     * Replace the body of an error response with a JSON:API error document.
     *
     * Exceptions thrown by the route were already rendered by the routing pipeline,
     * which keeps the exception on the response.
     */
    protected function formatErrorResponse(Response $response): Response
    {
        if ((! $response->isClientError() && ! $response->isServerError()) || $this->isJsonApiError($response)) {
            return $response;
        }

        $exception = $response instanceof JsonResponse ? $response->exception : null;
        $errorResponse = $exception instanceof ValidationException
            ? $this->validationErrorResponse($exception)
            : $this->generateJsonApiErrorResponse($response->getStatusCode(), $this->originalMessage($response));

        if ($errorResponse === null) {
            return $response;
        }

        foreach (self::PRESERVED_HEADERS as $header) {
            if ($response->headers->has($header)) {
                $errorResponse->headers->set($header, $response->headers->get($header));
            }
        }

        return $errorResponse;
    }

    /**
     * @param  string|null  $detail  message for statuses without a fixed detail, only used for client errors
     */
    protected function generateJsonApiErrorResponse(int $code, ?string $detail = null): ?Response
    {
        return match ($code) {
            401 => $this->toJsonApiResponse(401, 'Unauthorized', 'A valid bearer token is required to access this resource.'),
            403 => $this->toJsonApiResponse(403, 'Forbidden', 'You do not have the necessary permissions to access this resource.'),
            404 => $this->toJsonApiResponse(404, 'Not Found', 'The requested resource could not be found.'),
            429 => $this->toJsonApiResponse(429, 'Too Many Requests', 'Too many requests. Please try again later.'),
            503 => $this->toJsonApiResponse(503, 'Service Unavailable', 'A required service is unavailable.'),
            default => $this->genericErrorResponse($code, $detail),
        };
    }

    /**
     * Client errors keep their message. Server errors get a generic one, unless
     * debugging, where Laravel's response with the exception details is kept.
     */
    protected function genericErrorResponse(int $code, ?string $detail): ?Response
    {
        if ($code < 400 || ($code >= 500 && config('app.debug'))) {
            return null;
        }

        $title = Response::$statusTexts[$code] ?? 'Error';
        if ($code >= 500 || empty($detail)) {
            $detail = $code >= 500 ? 'An unexpected error occurred.' : $title;
        }

        return $this->toJsonApiResponse($code, $title, $detail);
    }

    /**
     * One error object per failed rule, with an RFC 6901 JSON Pointer to the attribute
     * that failed. Laravel separates nested attribute names with dots.
     */
    protected function validationErrorResponse(ValidationException $exception): Response
    {
        $errors = [];
        foreach ($exception->errors() as $attribute => $messages) {
            $segments = array_map(fn (string $segment): string => str_replace(['~', '/'], ['~0', '~1'], $segment), explode('.', (string) $attribute));
            foreach ($messages as $message) {
                $errors[] = [
                    'status' => (string) $exception->status,
                    'title' => Response::$statusTexts[$exception->status] ?? 'Unprocessable Content',
                    'detail' => $message,
                    'source' => ['pointer' => '/' . implode('/', $segments)],
                ];
            }
        }

        return $this->jsonApiErrors($errors, $exception->status);
    }

    /**
     * Format the response to adhere strictly to JSON:API specs.
     */
    protected function toJsonApiResponse(int $code, string $title, string $detail): Response
    {
        return $this->jsonApiErrors([
            [
                'status' => "$code", // JSON:API enforces string here
                'title' => $title,
                'detail' => $detail,
            ],
        ], $code);
    }

    private function isJsonApiError(Response $response): bool
    {
        $errors = $response instanceof JsonResponse ? ($response->getData(true)['errors'] ?? null) : null;

        return is_array($errors) && array_is_list($errors);
    }

    private function originalMessage(Response $response): ?string
    {
        if (! $response instanceof JsonResponse) {
            return null;
        }

        $message = $response->getData(true)['message'] ?? null;

        return is_string($message) ? $message : null;
    }

    /**
     * @param  list<array{status: string, title: string, detail: string, source?: array{pointer: string}}>  $errors
     */
    private function jsonApiErrors(array $errors, int $code): Response
    {
        return response()->json([
            'errors' => $errors,
        ], $code, [
            'Content-Type' => 'application/vnd.api+json',
        ]);
    }
}
