<?php

namespace App\Restify;

use App\Models\Syslog;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class SyslogRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Syslog::class;

    public static string $id = 'seq';

    public static string $title = 'msg';

    public static array $search = [
        'msg',
        'program',
        'tag',
    ];

    public static array $match = [
        'device_id' => 'integer',
        'facility' => 'text',
        'priority' => 'text',
        'level' => 'text',
        'tag' => 'text',
        'timestamp' => 'datetime',
        'program' => 'text',
        'msg' => 'text',
    ];

    public static array $sort = [
        'device_id',
        'facility',
        'priority',
        'level',
        'tag',
        'timestamp',
        'program',
        'msg',
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
            field('facility')->readonly(),
            field('priority')->readonly(),
            field('level')->readonly(),
            field('tag')->readonly(),
            field('timestamp')->readonly(),
            field('program')->readonly(),
            field('msg')->readonly(),
        ];
    }

    /**
     * Syslog entries are received from devices via syslog — not created manually via the API.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Syslog entries are historical records and should not be deleted via the API.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
