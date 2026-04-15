<?php

namespace App\Restify;

use App\Models\Vrf;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class VrfRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Vrf::class;

    /**
     * Override Restify's default pluralization which generates "vrves" instead of "vrfs".
     */
    public static string $uriKey = 'vrfs';

    public static string $id = 'vrf_id';

    public static string $title = 'vrf_name';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('vrf_name'),
        ];
    }

    public static function matches(): array
    {
        return [
            'oid' => MatchFilter::make()->setType('text')->setColumn('vrf_oid'),
            'name' => MatchFilter::make()->setType('text')->setColumn('vrf_name'),
            'bgpLocalAs' => MatchFilter::make()->setType('integer')->setColumn('bgpLocalAs'),
            'routeDistinguisher' => MatchFilter::make()->setType('text')->setColumn('mplsVpnVrfRouteDistinguisher'),
            'description' => MatchFilter::make()->setType('text')->setColumn('mplsVpnVrfDescription'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'oid' => SortableFilter::make()->setColumn('vrf_oid'),
            'name' => SortableFilter::make()->setColumn('vrf_name'),
            'bgpLocalAs' => SortableFilter::make()->setColumn('bgpLocalAs'),
            'routeDistinguisher' => SortableFilter::make()->setColumn('mplsVpnVrfRouteDistinguisher'),
            'description' => SortableFilter::make()->setColumn('mplsVpnVrfDescription'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('oid', fn ($value, $model) => $model->vrf_oid)->readonly(),
            field('name', fn ($value, $model) => $model->vrf_name)->readonly(),
            field('bgpLocalAs')->readonly(),
            field('routeDistinguisher', fn ($value, $model) => $model->mplsVpnVrfRouteDistinguisher)->readonly(),
            field('description', fn ($value, $model) => $model->mplsVpnVrfDescription)->readonly(),
        ];
    }

    /**
     * VRFs are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * VRFs are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
