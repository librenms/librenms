<?php

namespace App\Restify;

use App\Models\VrfLite;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class VrfLiteRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = VrfLite::class;

    public static string $uriKey = 'vrf-lite';

    public static string $id = 'vrf_lite_cisco_id';

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
            'contextName' => MatchFilter::make()->setType('text')->setColumn('context_name'),
            'instanceName' => MatchFilter::make()->setType('text')->setColumn('intance_name'),
            'name' => MatchFilter::make()->setType('text')->setColumn('vrf_name'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'contextName' => SortableFilter::make()->setColumn('context_name'),
            'instanceName' => SortableFilter::make()->setColumn('intance_name'),
            'name' => SortableFilter::make()->setColumn('vrf_name'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('contextName', fn ($value, $model) => $model->context_name)->readonly(),
            field('instanceName', fn ($value, $model) => $model->intance_name)->readonly(),
            field('name', fn ($value, $model) => $model->vrf_name)->readonly(),
        ];
    }

    /**
     * VRF Lite entries are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * VRF Lite entries are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
