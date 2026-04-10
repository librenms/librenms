<?php

namespace App\Restify;

use App\Models\IsisAdjacency;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class IsisAdjacencyRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = IsisAdjacency::class;

    public static string $title = 'isisISAdjNeighSysID';

    public static array $search = [
        'isisISAdjNeighSysID',
        'isisISAdjIPAddrAddress',
    ];

    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->readonly(),
            field('port_id')->readonly(),
            field('ifIndex')->readonly(),
            field('index')->readonly(),
            field('isisISAdjState')->readonly(),
            field('isisISAdjNeighSysType')->readonly(),
            field('isisISAdjNeighSysID')->readonly(),
            field('isisISAdjNeighPriority')->readonly(),
            field('isisISAdjLastUpTime')->readonly(),
            field('isisISAdjAreaAddress')->readonly(),
            field('isisISAdjIPAddrType')->readonly(),
            field('isisISAdjIPAddrAddress')->readonly(),
            field('isisCircAdminState')->readonly(),
        ];
    }

    /**
     * ISIS adjacencies are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * ISIS adjacencies are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
