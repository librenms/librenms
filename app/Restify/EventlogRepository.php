<?php

namespace App\Restify;

use App\Models\Eventlog;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class EventlogRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Eventlog::class;

    public static string $id = 'event_id';

    public static string $title = 'message';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'message' => SearchableFilter::make()->setColumn('message'),
        ];
    }

    public static function matches(): array
    {
        return [
            'createdAt' => MatchFilter::make()->setType('datetime')->setColumn('datetime'),
            'message' => MatchFilter::make()->setType('text')->setColumn('message'),
            'category' => MatchFilter::make()->setType('text')->setColumn('type'),
            'reference' => MatchFilter::make()->setType('text')->setColumn('reference'),
            'username' => MatchFilter::make()->setType('text')->setColumn('username'),
            'severity' => MatchFilter::make()->setType('integer')->setColumn('severity'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'createdAt' => SortableFilter::make()->setColumn('datetime'),
            'message' => SortableFilter::make()->setColumn('message'),
            'category' => SortableFilter::make()->setColumn('type'),
            'reference' => SortableFilter::make()->setColumn('reference'),
            'username' => SortableFilter::make()->setColumn('username'),
            'severity' => SortableFilter::make()->setColumn('severity'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('createdAt', fn ($value, $model) => $model->datetime)->readonly(),
            field('message')->readonly(),
            field('category', fn ($value, $model) => $model->type)->readonly(),
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
