<?php

namespace App\Restify;

use App\Models\DeviceOutage;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class DeviceOutageRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = DeviceOutage::class;

    public static string $title = 'going_down';

    public static array $match = [
        'device_id' => 'integer',
        'going_down' => 'integer',
        'up_again' => 'integer',
    ];

    public static array $sort = [
        'device_id',
        'going_down',
        'up_again',
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
            field('going_down')->readonly(),
            field('up_again')->readonly(),
        ];
    }

    /**
     * Device outages are recorded automatically by LibreNMS during polling — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Device outages are historical records and should not be deleted via the API.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
