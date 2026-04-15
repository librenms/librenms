<?php

namespace App\Restify;

use App\Models\Application;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class ApplicationRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Application::class;

    public static string $id = 'app_id';

    public static string $title = 'app_type';




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
            'category' => MatchFilter::make()->setType('text')->setColumn('app_type'),
            'instance' => MatchFilter::make()->setType('text')->setColumn('app_instance'),
            'status' => MatchFilter::make()->setType('text')->setColumn('app_status'),
            'state' => MatchFilter::make()->setType('text')->setColumn('app_state'),
            'createdAt' => MatchFilter::make()->setType('datetime')->setColumn('discovered'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'category' => SortableFilter::make()->setColumn('app_type'),
            'instance' => SortableFilter::make()->setColumn('app_instance'),
            'status' => SortableFilter::make()->setColumn('app_status'),
            'state' => SortableFilter::make()->setColumn('app_state'),
            'createdAt' => SortableFilter::make()->setColumn('discovered'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('category', fn ($value, $model) => $model->app_type)->readonly(),
            field('instance', fn ($value, $model) => $model->app_instance)->readonly(),
            field('status', fn ($value, $model) => $model->app_status)->readonly(),
            field('state', fn ($value, $model) => $model->app_state)->readonly(),
            field('createdAt', fn ($value, $model) => $model->discovered)->readonly(),
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
