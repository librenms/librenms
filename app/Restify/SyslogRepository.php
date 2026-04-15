<?php

namespace App\Restify;

use App\Models\Syslog;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class SyslogRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Syslog::class;

    public static string $id = 'seq';

    public static string $title = 'msg';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'message' => SearchableFilter::make()->setColumn('msg'),
        ];
    }

    public static function matches(): array
    {
        return [
            'facility' => MatchFilter::make()->setType('text')->setColumn('facility'),
            'priority' => MatchFilter::make()->setType('text')->setColumn('priority'),
            'level' => MatchFilter::make()->setType('text')->setColumn('level'),
            'tag' => MatchFilter::make()->setType('text')->setColumn('tag'),
            'createdAt' => MatchFilter::make()->setType('datetime')->setColumn('timestamp'),
            'program' => MatchFilter::make()->setType('text')->setColumn('program'),
            'message' => MatchFilter::make()->setType('text')->setColumn('msg'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'facility' => SortableFilter::make()->setColumn('facility'),
            'priority' => SortableFilter::make()->setColumn('priority'),
            'level' => SortableFilter::make()->setColumn('level'),
            'tag' => SortableFilter::make()->setColumn('tag'),
            'createdAt' => SortableFilter::make()->setColumn('timestamp'),
            'program' => SortableFilter::make()->setColumn('program'),
            'message' => SortableFilter::make()->setColumn('msg'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('facility')->readonly(),
            field('priority')->readonly(),
            field('level')->readonly(),
            field('tag')->readonly(),
            field('createdAt', fn ($value, $model) => $model->timestamp)->readonly(),
            field('program')->readonly(),
            field('message', fn ($value, $model) => $model->msg)->readonly(),
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
