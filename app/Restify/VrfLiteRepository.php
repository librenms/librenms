<?php

namespace App\Restify;

use App\Models\VrfLite;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class VrfLiteRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = VrfLite::class;

    public static string $id = 'vrf_lite_cisco_id';

    public static string $title = 'vrf_name';

    public static array $search = [
        'vrf_name',
        'context_name',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'context_name' => 'text',
        'intance_name' => 'text',
        'vrf_name' => 'text',
    ];

    public static array $sort = [
        'device_id',
        'context_name',
        'intance_name',
        'vrf_name',
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
            field('context_name')->readonly(),
            field('intance_name')->readonly(),
            field('vrf_name')->readonly(),
        ];
    }

    /**
     * VRF Lite entries are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * VRF Lite entries are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
