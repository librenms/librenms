<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Api\OpenApi\OpenApiGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class OpenApiController extends Controller
{
    public function __construct(private readonly OpenApiGenerator $generator)
    {
    }

    public function spec(Request $request): JsonResponse
    {
        $array = $request->boolean('fresh')
            ? $this->generator->generate()->toArray()
            : Cache::remember(
                'api.v1.openapi.spec',
                300,
                fn () => $this->generator->generate()->toArray(),
            );

        return new JsonResponse($array);
    }

    public function docs(): View
    {
        return view('api.v1.docs', [
            'specUrl' => route('v1.openapi'),
            'cdnUrl' => 'https://cdn.jsdelivr.net/npm/swagger-ui-dist@5',
        ]);
    }
}
