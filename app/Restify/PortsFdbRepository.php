<?php

namespace App\Restify;

use App\Models\PortsFdb;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class PortsFdbRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortsFdb::class;

    public static string $id = 'ports_fdb_id';

    public static string $title = 'mac_address';

    public static array $search = [
        'mac_address',
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
            field('port_id')->readonly(),
            field('mac_address')->readonly(),
            field('vlan_id')->readonly(),
            field('device_id')->readonly(),
            field('updated_at')->readonly(),
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
