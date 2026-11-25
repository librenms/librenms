<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Middleware\EnforceJsonApi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\AnonymousResourceCollection;

/**
 * A v1 resource collection, paginated with JSON:API page[number] and page[size] links.
 */
class ResourceCollection extends AnonymousResourceCollection
{
    /**
     * Top-level members of a resource collection document.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function with($request): array
    {
        return [
            ...parent::with($request),
            'jsonapi' => ['version' => EnforceJsonApi::VERSION],
        ];
    }

    /**
     * @param  array{current_page: int, last_page: int, per_page: int, total: int}  $paginated
     * @param  array<string, mixed>  $default
     * @return array{links: array<string, string|null>, meta: array{page: array<string, int>}}
     */
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        $current = $paginated['current_page'];
        $last = max(1, $paginated['last_page']);
        $link = fn (int $number): string => $request->fullUrlWithQuery(['page' => ['number' => $number, 'size' => $paginated['per_page']]]);

        return [
            'links' => [
                'self' => $request->fullUrl(),
                'first' => $link(1),
                'last' => $link($last),
                'prev' => $current > 1 ? $link(min($current - 1, $last)) : null,
                'next' => $current < $last ? $link($current + 1) : null,
            ],
            'meta' => [
                'page' => [
                    'currentPage' => $current,
                    'lastPage' => $last,
                    'perPage' => $paginated['per_page'],
                    'total' => $paginated['total'],
                ],
            ],
        ];
    }
}
