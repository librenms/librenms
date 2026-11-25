<?php

namespace App\Restify;

use App\Models\IsisAdjacency;
use App\Models\Port;
use Binaryk\LaravelRestify\Fields\Field;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Binaryk\LaravelRestify\Restify;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @property IsisAdjacency $resource
 */
class IsisAdjacencyRepository extends Repository
{
    public static string $model = IsisAdjacency::class;

    public static string $title = 'isisISAdjNeighSysID';

    /** @var list<string> */
    public static array $search = ['isisISAdjNeighSysID', 'isisISAdjIPAddrAddress'];

    /** @var list<string> */
    public static array $sort = ['id', 'device_id', 'port_id', 'ifIndex', 'isisISAdjState'];

    /**
     * Served under /api/v1/routing/isis-adjacencies, grouping it with the other routing
     * resources. The prefix also confines the repository to that path: the generic
     * /api/v1/isis-adjacencies route is no longer authorized.
     */
    public static function prefix(): ?string
    {
        return trim(Restify::path('routing'), '/');
    }

    /**
     * Scope to the adjacencies the user can access (routing.viewAll sees all, otherwise those on
     * ports or devices they can access), the same hasAccess scope the web IS-IS page uses. The
     * view policy applies the matching per-row check.
     *
     * @param  Builder<IsisAdjacency>|Relation<IsisAdjacency, Model, mixed>  $query
     * @return Builder<IsisAdjacency>|Relation<IsisAdjacency, Model, mixed>
     */
    public static function indexQuery(RestifyRequest $request, Builder|Relation $query): Builder|Relation
    {
        return $query->hasAccess($request->user())->with('port');
    }

    /**
     * @param  Builder<IsisAdjacency>|Relation<IsisAdjacency, Model, mixed>  $query
     * @return Builder<IsisAdjacency>|Relation<IsisAdjacency, Model, mixed>
     */
    public static function showQuery(RestifyRequest $request, Builder|Relation $query): Builder|Relation
    {
        return $query->hasAccess($request->user())->with('port');
    }

    /**
     * The query parameter for a match is the column, so these filter on
     * ?device_id=, ?port_id=, ?ifIndex= and ?isisISAdjState=.
     *
     * @return array<string, MatchFilter>
     */
    public static function matches(): array
    {
        return [
            'device_id' => MatchFilter::make()->setType('integer')->setColumn('device_id'),
            'port_id' => MatchFilter::make()->setType('integer')->setColumn('port_id'),
            'ifIndex' => MatchFilter::make()->setType('integer')->setColumn('ifIndex'),
            'isisISAdjState' => MatchFilter::make()->setType('text')->setColumn('isisISAdjState'),
        ];
    }

    /**
     * @return array<int, Field>
     */
    public function fields(RestifyRequest $request): array
    {
        return [
            field('deviceId', fn (mixed $value, self $repository): int => (int) $repository->resource->device_id)->readonly(),
            field('portId', fn (mixed $value, self $repository): ?int => $repository->resource->port_id)->readonly(),
            field('ifIndex')->readonly(),
            field('port', fn (mixed $value, self $repository): ?array => self::portAttributes($repository->resource->port))->readonly(),
            field('index')->readonly(),
            field('isisCircAdminState')->readonly(),
            field('isisISAdjState')->readonly(),
            field('isisISAdjNeighSysType')->readonly(),
            field('isisISAdjNeighSysID')->readonly(),
            field('isisISAdjNeighPriority')->readonly(),
            field('isisISAdjLastUpTime')->readonly(),
            field('isisISAdjAreaAddress')->readonly(),
            field('isisISAdjIPAddrType')->readonly(),
            field('isisISAdjIPAddrAddress')->readonly(),
        ];
    }

    /**
     * The local port the adjacency is on, embedded as a nested object. Related resources are not
     * yet exposed as JSON:API relationships in v1, so the useful port fields are included inline.
     *
     * @return array<string, mixed>|null
     */
    private static function portAttributes(?Port $port): ?array
    {
        if ($port === null) {
            return null;
        }

        return [
            'id' => $port->port_id,
            'ifName' => $port->ifName,
            'ifDescr' => $port->ifDescr,
            'ifAlias' => $port->ifAlias,
        ];
    }
}
