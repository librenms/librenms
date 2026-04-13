<?php

namespace App\Restify;

use App\Models\AccessPoint;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class AccessPointRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = AccessPoint::class;

    public static string $id = 'accesspoint_id';

    public static string $title = 'name';

    public static array $search = [
        'name',
        'mac_addr',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'name' => 'text',
        'radio_number' => 'integer',
        'type' => 'text',
        'mac_addr' => 'text',
        'channel' => 'integer',
        'txpow' => 'integer',
        'radioutil' => 'integer',
        'numasoclients' => 'integer',
        'nummonclients' => 'integer',
        'numactbssid' => 'integer',
        'nummonbssid' => 'integer',
        'interference' => 'integer',
    ];

    public static array $sort = [
        'device_id',
        'name',
        'radio_number',
        'type',
        'mac_addr',
        'channel',
        'txpow',
        'radioutil',
        'numasoclients',
        'nummonclients',
        'numactbssid',
        'nummonbssid',
        'interference',
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
            field('name')->readonly(),
            field('radio_number')->readonly(),
            field('type')->readonly(),
            field('mac_addr')->readonly(),
            field('channel')->readonly(),
            field('txpow')->readonly(),
            field('radioutil')->readonly(),
            field('numasoclients')->readonly(),
            field('nummonclients')->readonly(),
            field('numactbssid')->readonly(),
            field('nummonbssid')->readonly(),
            field('interference')->readonly(),
        ];
    }

    /**
     * Access points are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Access points are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
