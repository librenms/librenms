<?php

namespace App\Restify;

use App\Models\AlertLog;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class AlertLogRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = AlertLog::class;

    public static string $title = 'id';



    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
            'rule' => BelongsTo::make('rule', AlertRuleRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [];
    }

    public static function matches(): array
    {
        return [
            'state' => MatchFilter::make()->setType('integer')->setColumn('state'),
            'createdAt' => MatchFilter::make()->setType('datetime')->setColumn('time_logged'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'state' => SortableFilter::make()->setColumn('state'),
            'createdAt' => SortableFilter::make()->setColumn('time_logged'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('state')->readonly(),
            field('createdAt', fn ($value, $model) => $model->time_logged)->readonly(),
        ];
    }

    /**
     * Alert logs are generated internally by the alerting engine not created manually via the API.
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
