<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Middleware\EnforceJsonApi;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * A JSON:API document without resources, only meta, such as /system.
 */
final readonly class MetaDocument implements Responsable
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(private array $meta)
    {
    }

    /**
     * @param  Request  $request
     */
    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'jsonapi' => ['version' => EnforceJsonApi::VERSION],
            'links' => ['self' => $request->fullUrl()],
            'meta' => $this->meta,
        ]);
    }
}
