<?php

namespace App\Restify;

use App\Models\DeviceOutage;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class DeviceOutageRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = DeviceOutage::class;

    public static string $title = 'going_down';



    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [];
    }

    public static function matches(): array
    {
        return [
            'outageStartAt' => MatchFilter::make()->setType('datetime')->setColumn('going_down'),
            'outageEndAt' => MatchFilter::make()->setType('datetime')->setColumn('up_again'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'outageStartAt' => SortableFilter::make()->setColumn('going_down'),
            'outageEndAt' => SortableFilter::make()->setColumn('up_again'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('outageStartAt', fn ($value, $model) => $model->going_down)->readonly(),
            field('outageEndAt', fn ($value, $model) => $model->up_again)->readonly(),
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
