<?php

namespace App\Restify;

use App\Models\PortVlan;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class PortVlanRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortVlan::class;

    public static string $id = 'port_vlan_id';

    public static string $title = 'vlan';

    public static array $search = [
        'vlan',
        'state',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'port_id' => 'integer',
        'vlan' => 'integer',
        'baseport' => 'integer',
        'priority' => 'integer',
        'state' => 'text',
        'cost' => 'integer',
        'untagged' => 'integer',
    ];

    public static array $sort = [
        'device_id',
        'port_id',
        'vlan',
        'baseport',
        'priority',
        'state',
        'cost',
        'untagged',
    ];

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->readonly(),
            field('port_id')->readonly(),
            field('vlan')->readonly(),
            field('baseport')->readonly(),
            field('priority')->readonly(),
            field('state')->readonly(),
            field('cost')->readonly(),
            field('untagged')->readonly(),
        ];
    }

    /**
     * Port VLAN assignments are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Port VLAN assignments are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
