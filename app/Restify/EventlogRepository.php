<?php

namespace App\Restify;

use App\Models\Eventlog;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class EventlogRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Eventlog::class;

    public static string $id = 'event_id';

    public static string $title = 'message';

    public static array $search = [
        'message',
        'type',
        'username',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'datetime' => 'datetime',
        'type' => 'text',
        'reference' => 'text',
        'username' => 'text',
        'severity' => 'integer',
    ];

    public static array $sort = [
        'device_id',
        'datetime',
        'type',
        'reference',
        'username',
        'severity',
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
            field('datetime')->readonly(),
            field('message')->readonly(),
            field('type')->readonly(),
            field('reference')->readonly(),
            field('username')->readonly(),
            field('severity')->readonly(),
        ];
    }

    /**
     * Event logs are generated internally by LibreNMS — not created manually via the API.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Event logs are historical records and should not be deleted via the API.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
