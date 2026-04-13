<?php

namespace App\Restify;

use App\Models\PortStack;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class PortStackRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = PortStack::class;

    public static string $title = 'ifStackStatus';

    public static array $match = [
        'device_id' => 'integer',
        'high_ifIndex' => 'integer',
        'high_port_id' => 'integer',
        'low_ifIndex' => 'integer',
        'low_port_id' => 'integer',
        'ifStackStatus' => 'text',
    ];

    public static array $sort = [
        'device_id',
        'high_ifIndex',
        'high_port_id',
        'low_ifIndex',
        'low_port_id',
        'ifStackStatus',
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
            field('high_ifIndex')->readonly(),
            field('high_port_id')->readonly(),
            field('low_ifIndex')->readonly(),
            field('low_port_id')->readonly(),
            field('ifStackStatus')->readonly(),
        ];
    }

    /**
     * Port stacking relationships are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Port stacking relationships are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
