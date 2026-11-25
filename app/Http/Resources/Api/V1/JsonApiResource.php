<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Middleware\EnforceJsonApi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiRequest;
use Illuminate\Http\Resources\JsonApi\JsonApiResource as BaseJsonApiResource;
use Illuminate\Support\Collection;

/**
 * Base for v1 resources.
 *
 * Every relationship gets a related link, its data is only present when it is included.
 * Included resources are authorized with their own policy's view ability, so a
 * relationship never reveals more than its own endpoint would.
 */
abstract class JsonApiResource extends BaseJsonApiResource
{
    /**
     * Related resource links, keyed by relationship name.
     *
     * @return array<string, string>
     */
    protected function toRelationshipLinks(Request $request): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveResourceRelationshipIdentifiers(JsonApiRequest $request): array
    {
        $relationships = parent::resolveResourceRelationshipIdentifiers($request);

        foreach ($this->toRelationshipLinks($request) as $name => $url) {
            $relationships[$name] = ['links' => ['related' => $url], ...$relationships[$name] ?? []];
        }

        return $relationships;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function resolveIncludedResourceObjects(JsonApiRequest $request): Collection
    {
        $this->compileResourceRelationships($request);

        $user = $request->user();
        $this->loadedRelationshipsMap = array_values(array_filter(
            $this->loadedRelationshipsMap ?? [],
            fn (array $loaded): bool => (bool) $user?->can('view', $loaded[0]->resource),
        ));

        return parent::resolveIncludedResourceObjects($request);
    }

    /**
     * Top-level members of a single resource document.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function with($request): array
    {
        return [
            'links' => ['self' => $request->fullUrl()],
            ...parent::with($request),
            'jsonapi' => ['version' => EnforceJsonApi::VERSION],
        ];
    }

    /**
     * @param  mixed  $resource
     */
    public static function collection($resource): ResourceCollection
    {
        $collection = parent::collection($resource);
        assert($collection instanceof ResourceCollection);

        return $collection;
    }

    /**
     * @param  mixed  $resource
     */
    protected static function newCollection($resource): ResourceCollection
    {
        return new ResourceCollection($resource, static::class);
    }
}
