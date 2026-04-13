<?php

namespace App\Restify;

use App\Models\AlertLog;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;

class AlertLogRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = AlertLog::class;

    public static string $title = 'id';

    public static array $match = [
        'device_id' => 'integer',
        'rule_id' => 'integer',
        'state' => 'integer',
        'time_logged' => 'datetime',
    ];

    public static array $sort = [
        'device_id',
        'rule_id',
        'state',
        'time_logged',
    ];

    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
            'rule' => BelongsTo::make('rule', AlertRuleRepository::class),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->readonly(),
            field('rule_id')->readonly(),
            field('state')->readonly(),
            field('time_logged')->readonly(),
        ];
    }

    /**
     * Alert logs are generated internally by the alerting engine — not created manually via the API.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Alert logs are historical records and should not be deleted via the API.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
