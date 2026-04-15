<?php

namespace App\Restify;

use App\Models\Route;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class RouteRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Route::class;

    public static string $id = 'route_id';

    public static string $title = 'inetCidrRouteDest';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'destination' => SearchableFilter::make()->setColumn('inetCidrRouteDest'),
        ];
    }

    public static function matches(): array
    {
        return [
            'contextName' => MatchFilter::make()->setType('text')->setColumn('context_name'),
            'interfaceIndex' => MatchFilter::make()->setType('integer')->setColumn('inetCidrRouteIfIndex'),
            'destination' => MatchFilter::make()->setType('text')->setColumn('inetCidrRouteDest'),
            'destinationCategory' => MatchFilter::make()->setType('text')->setColumn('inetCidrRouteDestType'),
            'prefixLength' => MatchFilter::make()->setType('integer')->setColumn('inetCidrRoutePfxLen'),
            'policy' => MatchFilter::make()->setType('text')->setColumn('inetCidrRoutePolicy'),
            'nextHop' => MatchFilter::make()->setType('text')->setColumn('inetCidrRouteNextHop'),
            'nextHopCategory' => MatchFilter::make()->setType('text')->setColumn('inetCidrRouteNextHopType'),
            'metric1' => MatchFilter::make()->setType('integer')->setColumn('inetCidrRouteMetric1'),
            'protocol' => MatchFilter::make()->setType('text')->setColumn('inetCidrRouteProto'),
            'category' => MatchFilter::make()->setType('text')->setColumn('inetCidrRouteType'),
            'nextHopAs' => MatchFilter::make()->setType('integer')->setColumn('inetCidrRouteNextHopAS'),
            'updatedAt' => MatchFilter::make()->setType('datetime')->setColumn('updated_at'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'contextName' => SortableFilter::make()->setColumn('context_name'),
            'interfaceIndex' => SortableFilter::make()->setColumn('inetCidrRouteIfIndex'),
            'destination' => SortableFilter::make()->setColumn('inetCidrRouteDest'),
            'destinationCategory' => SortableFilter::make()->setColumn('inetCidrRouteDestType'),
            'prefixLength' => SortableFilter::make()->setColumn('inetCidrRoutePfxLen'),
            'policy' => SortableFilter::make()->setColumn('inetCidrRoutePolicy'),
            'nextHop' => SortableFilter::make()->setColumn('inetCidrRouteNextHop'),
            'nextHopCategory' => SortableFilter::make()->setColumn('inetCidrRouteNextHopType'),
            'metric1' => SortableFilter::make()->setColumn('inetCidrRouteMetric1'),
            'protocol' => SortableFilter::make()->setColumn('inetCidrRouteProto'),
            'category' => SortableFilter::make()->setColumn('inetCidrRouteType'),
            'nextHopAs' => SortableFilter::make()->setColumn('inetCidrRouteNextHopAS'),
            'updatedAt' => SortableFilter::make()->setColumn('updated_at'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('contextName', fn ($value, $model) => $model->context_name)->readonly(),
            field('interfaceIndex', fn ($value, $model) => $model->inetCidrRouteIfIndex)->readonly(),
            field('destination', fn ($value, $model) => $model->inetCidrRouteDest)->readonly(),
            field('destinationCategory', fn ($value, $model) => $model->inetCidrRouteDestType)->readonly(),
            field('prefixLength', fn ($value, $model) => $model->inetCidrRoutePfxLen)->readonly(),
            field('policy', fn ($value, $model) => $model->inetCidrRoutePolicy)->readonly(),
            field('nextHop', fn ($value, $model) => $model->inetCidrRouteNextHop)->readonly(),
            field('nextHopCategory', fn ($value, $model) => $model->inetCidrRouteNextHopType)->readonly(),
            field('metric1', fn ($value, $model) => $model->inetCidrRouteMetric1)->readonly(),
            field('protocol', fn ($value, $model) => $model->inetCidrRouteProto)->readonly(),
            field('category', fn ($value, $model) => $model->inetCidrRouteType)->readonly(),
            field('nextHopAs', fn ($value, $model) => $model->inetCidrRouteNextHopAS)->readonly(),
            field('updatedAt', fn ($value, $model) => $model->updated_at)->readonly(),
        ];
    }

    /**
     * Routes are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Routes are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
