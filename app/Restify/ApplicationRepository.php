<?php

namespace App\Restify;

use App\Models\Application;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class ApplicationRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Application::class;

    public static string $id = 'app_id';

    public static string $title = 'app_type';

    public static array $search = [
        'app_type',
        'app_instance',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'app_type' => 'text',
        'app_instance' => 'text',
        'app_status' => 'text',
        'app_state' => 'text',
        'discovered' => 'integer',
    ];

    public static array $sort = [
        'device_id',
        'app_type',
        'app_instance',
        'app_status',
        'app_state',
        'discovered',
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
            field('app_type')->readonly(),
            field('app_instance')->readonly(),
            field('app_status')->readonly(),
            field('app_state')->readonly(),
            field('discovered')->readonly(),
        ];
    }

    /**
     * Applications are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Applications are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
