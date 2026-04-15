<?php

namespace App\Restify;

use App\Models\DiskIo;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class DiskIoRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = DiskIo::class;

    public static string $id = 'diskio_id';

    public static string $title = 'diskio_descr';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'description' => SearchableFilter::make()->setColumn('diskio_descr'),
        ];
    }

    public static function matches(): array
    {
        return [
            'index' => MatchFilter::make()->setType('integer')->setColumn('diskio_index'),
            'description' => MatchFilter::make()->setType('text')->setColumn('diskio_descr'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'index' => SortableFilter::make()->setColumn('diskio_index'),
            'description' => SortableFilter::make()->setColumn('diskio_descr'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('index', fn ($value, $model) => $model->diskio_index)->readonly(),
            field('description', fn ($value, $model) => $model->diskio_descr)->readonly(),
        ];
    }

    /**
     * Disk I/O entries are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Disk I/O entries are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
