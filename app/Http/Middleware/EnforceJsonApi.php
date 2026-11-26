<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * JSON:API content negotiation and query parameter rules.
 * No extensions are supported, profiles are ignored.
 */
class EnforceJsonApi
{
    public const CONTENT_TYPE = 'application/vnd.api+json';
    public const VERSION = '1.1';

    /**
     * Query parameters defined by JSON:API, other all lowercase names are reserved.
     */
    private const array QUERY_PARAMETERS = ['include', 'fields', 'sort', 'page', 'filter'];

    public function handle(Request $request, Closure $next): Response
    {
        // a GET body has no meaning, Laravel's test client sends one anyway
        if (! $request->isMethodSafe() && $request->getContent() !== '' && ! $this->isSupported($request->header('Content-Type', ''))) {
            abort(415, 'Request bodies must use the ' . self::CONTENT_TYPE . ' media type, without parameters other than profile.');
        }

        if (! $this->isAcceptable($request->header('Accept', ''))) {
            abort(406, 'The ' . self::CONTENT_TYPE . ' media type is only accepted with the profile parameter.');
        }

        foreach (array_keys($request->query()) as $parameter) {
            if (preg_match('/^[a-z]+$/', (string) $parameter) && ! in_array($parameter, self::QUERY_PARAMETERS, true)) {
                throw ValidationException::withMessages([$parameter => "Unsupported query parameter: $parameter"])->status(400);
            }
        }

        // Tell Laravel to treat this as a JSON request
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);
        $response->headers->set('Content-Type', self::CONTENT_TYPE);

        return $response;
    }

    private function isSupported(string $contentType): bool
    {
        [$type, $parameters] = $this->parseMediaType(HeaderUtils::split($contentType, ';='));

        return $type === self::CONTENT_TYPE && $this->hasSupportedParameters($parameters);
    }

    /**
     * Unacceptable only when every JSON:API media range has unsupported parameters.
     * Clients that don't ask for the JSON:API media type get it anyway.
     */
    private function isAcceptable(string $accept): bool
    {
        $jsonApiRanges = array_filter(
            array_map($this->parseMediaType(...), HeaderUtils::split($accept, ',;=')),
            fn (array $range): bool => $range[0] === self::CONTENT_TYPE,
        );

        if (empty($jsonApiRanges)) {
            return true;
        }

        foreach ($jsonApiRanges as [, $parameters]) {
            unset($parameters['q']); // accept weight, not a media type parameter
            if ($this->hasSupportedParameters($parameters)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, string>  $parameters
     */
    private function hasSupportedParameters(array $parameters): bool
    {
        return array_keys($parameters) === [] || array_keys($parameters) === ['profile'];
    }

    /**
     * Quoted values may contain delimiters, such as a profile URI with a ; or a comma.
     *
     * @param  array<array<int, string>>  $parts  a media type split by HeaderUtils::split() on ;=
     * @return array{0: string, 1: array<string, string>}
     */
    private function parseMediaType(array $parts): array
    {
        $type = strtolower(array_shift($parts)[0] ?? '');

        $parameters = [];
        foreach ($parts as $part) {
            $parameters[strtolower($part[0])] = $part[1] ?? '';
        }

        return [$type, $parameters];
    }
}
