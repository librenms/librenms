<?php

namespace App\Restify;

use App\Models\Vlan;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class VlanRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Vlan::class;

    public static string $id = 'vlan_id';

    public static string $title = 'vlan_name';

    public static array $search = [
        'vlan_name',
        'vlan_vlan',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'vlan_vlan' => 'integer',
        'vlan_domain' => 'integer',
        'vlan_name' => 'text',
        'vlan_type' => 'text',
        'vlan_mtu' => 'text',
    ];

    public static array $sort = [
        'device_id',
        'vlan_vlan',
        'vlan_domain',
        'vlan_name',
        'vlan_type',
        'vlan_mtu',
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
            field('vlan_vlan')->readonly(),
            field('vlan_domain')->readonly(),
            field('vlan_name')->readonly(),
            field('vlan_type')->readonly(),
            field('vlan_mtu')->readonly(),
        ];
    }

    /**
     * VLANs are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * VLANs are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
