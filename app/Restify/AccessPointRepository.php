<?php

namespace App\Restify;

use App\Models\AccessPoint;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class AccessPointRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = AccessPoint::class;

    public static string $id = 'accesspoint_id';

    public static string $title = 'name';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('name'),
            'macAddress' => SearchableFilter::make()->setColumn('mac_addr'),
        ];
    }

    public static function matches(): array
    {
        return [
            'name' => MatchFilter::make()->setType('text')->setColumn('name'),
            'radioNumber' => MatchFilter::make()->setType('integer')->setColumn('radio_number'),
            'category' => MatchFilter::make()->setType('text')->setColumn('type'),
            'macAddress' => MatchFilter::make()->setType('text')->setColumn('mac_addr'),
            'channel' => MatchFilter::make()->setType('integer')->setColumn('channel'),
            'transmitPower' => MatchFilter::make()->setType('integer')->setColumn('txpow'),
            'radioUtilization' => MatchFilter::make()->setType('integer')->setColumn('radioutil'),
            'associatedClients' => MatchFilter::make()->setType('integer')->setColumn('numasoclients'),
            'monitoredClients' => MatchFilter::make()->setType('integer')->setColumn('nummonclients'),
            'activeBssidCount' => MatchFilter::make()->setType('integer')->setColumn('numactbssid'),
            'monitoredBssidCount' => MatchFilter::make()->setType('integer')->setColumn('nummonbssid'),
            'interference' => MatchFilter::make()->setType('integer')->setColumn('interference'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'name' => SortableFilter::make()->setColumn('name'),
            'radioNumber' => SortableFilter::make()->setColumn('radio_number'),
            'category' => SortableFilter::make()->setColumn('type'),
            'macAddress' => SortableFilter::make()->setColumn('mac_addr'),
            'channel' => SortableFilter::make()->setColumn('channel'),
            'transmitPower' => SortableFilter::make()->setColumn('txpow'),
            'radioUtilization' => SortableFilter::make()->setColumn('radioutil'),
            'associatedClients' => SortableFilter::make()->setColumn('numasoclients'),
            'monitoredClients' => SortableFilter::make()->setColumn('nummonclients'),
            'activeBssidCount' => SortableFilter::make()->setColumn('numactbssid'),
            'monitoredBssidCount' => SortableFilter::make()->setColumn('nummonbssid'),
            'interference' => SortableFilter::make()->setColumn('interference'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')->readonly(),
            field('radioNumber', fn ($value, $model) => $model->radio_number)->readonly(),
            field('category', fn ($value, $model) => $model->type)->readonly(),
            field('macAddress', fn ($value, $model) => $model->mac_addr)->readonly(),
            field('channel')->readonly(),
            field('transmitPower', fn ($value, $model) => $model->txpow)->readonly(),
            field('radioUtilization', fn ($value, $model) => $model->radioutil)->readonly(),
            field('associatedClients', fn ($value, $model) => $model->numasoclients)->readonly(),
            field('monitoredClients', fn ($value, $model) => $model->nummonclients)->readonly(),
            field('activeBssidCount', fn ($value, $model) => $model->numactbssid)->readonly(),
            field('monitoredBssidCount', fn ($value, $model) => $model->nummonbssid)->readonly(),
            field('interference')->readonly(),
        ];
    }

    /**
     * Access points are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Access points are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
