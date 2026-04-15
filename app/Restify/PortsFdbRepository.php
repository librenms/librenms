<?php

namespace App\Restify;

use App\Models\PortsFdb;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class PortsFdbRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortsFdb::class;

    public static string $id = 'ports_fdb_id';

    public static string $title = 'mac_address';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'macAddress' => SearchableFilter::make()->setColumn('mac_address'),
        ];
    }

    public static function matches(): array
    {
        return [
            'macAddress' => MatchFilter::make()->setType('text')->setColumn('mac_address'),
            'updatedAt' => MatchFilter::make()->setType('datetime')->setColumn('updated_at'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'macAddress' => SortableFilter::make()->setColumn('mac_address'),
            'updatedAt' => SortableFilter::make()->setColumn('updated_at'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('macAddress', fn ($value, $model) => $model->mac_address)->readonly(),
            field('updatedAt', fn ($value, $model) => $model->updated_at)->readonly(),
        ];
    }

    /**
     * FDB entries are discovered automatically by LibreNMS during the polling process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * FDB entries are managed by the LibreNMS polling process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
