<?php

namespace App\Restify;

use App\Models\PortSecurity;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class PortSecurityRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = PortSecurity::class;

    public static string $title = 'last_mac_address';

    public static array $search = [
        'last_mac_address',
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
            field('device_id')->readonly(),
            field('port_security_enable')->readonly(),
            field('status')->readonly(),
            field('max_addresses')->readonly(),
            field('address_count')->readonly(),
            field('violation_action')->readonly(),
            field('violation_count')->readonly(),
            field('last_mac_address')->readonly(),
            field('sticky_enable')->readonly(),
        ];
    }

    /**
     * Port security entries are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Port security entries are managed by the LibreNMS discovery process.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
