<?php

namespace App\Restify;

use App\Models\Storage;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class StorageRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Storage::class;

    public static $uriKey = 'storage';

    public static string $id = 'storage_id';

    public static string $title = 'storage_descr';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'description' => SearchableFilter::make()->setColumn('storage_descr'),
        ];
    }

    public static function matches(): array
    {
        return [
            'category' => MatchFilter::make()->setType('text')->setColumn('storage_type'),
            'description' => MatchFilter::make()->setType('text')->setColumn('storage_descr'),
            'size' => MatchFilter::make()->setType('integer')->setColumn('storage_size'),
            'units' => MatchFilter::make()->setType('integer')->setColumn('storage_units'),
            'used' => MatchFilter::make()->setType('integer')->setColumn('storage_used'),
            'free' => MatchFilter::make()->setType('integer')->setColumn('storage_free'),
            'percentage' => MatchFilter::make()->setType('integer')->setColumn('storage_perc'),
            'warningPercentage' => MatchFilter::make()->setType('integer')->setColumn('storage_perc_warn'),
            'index' => MatchFilter::make()->setType('integer')->setColumn('storage_index'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'category' => SortableFilter::make()->setColumn('storage_type'),
            'description' => SortableFilter::make()->setColumn('storage_descr'),
            'size' => SortableFilter::make()->setColumn('storage_size'),
            'units' => SortableFilter::make()->setColumn('storage_units'),
            'used' => SortableFilter::make()->setColumn('storage_used'),
            'free' => SortableFilter::make()->setColumn('storage_free'),
            'percentage' => SortableFilter::make()->setColumn('storage_perc'),
            'warningPercentage' => SortableFilter::make()->setColumn('storage_perc_warn'),
            'index' => SortableFilter::make()->setColumn('storage_index'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('category', fn ($value, $model) => $model->storage_type)->readonly(),
            field('description', fn ($value, $model) => $model->storage_descr)->readonly(),
            field('size', fn ($value, $model) => $model->storage_size)->readonly(),
            field('units', fn ($value, $model) => $model->storage_units)->readonly(),
            field('used', fn ($value, $model) => $model->storage_used)->readonly(),
            field('free', fn ($value, $model) => $model->storage_free)->readonly(),
            field('percentage', fn ($value, $model) => $model->storage_perc)->readonly(),
            field('warningPercentage', fn ($value, $model) => $model->storage_perc_warn)->readonly(),
            field('index', fn ($value, $model) => $model->storage_index)->readonly(),
        ];
    }

    /**
     * Storage entries are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Storage entries are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
