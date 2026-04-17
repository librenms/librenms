<?php

namespace App\Restify;

use App\Models\Mempool;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class MempoolRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Mempool::class;

    public static string $uriKey = 'memory-pools';

    public static string $id = 'mempool_id';

    public static string $title = 'mempool_descr';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'description' => SearchableFilter::make()->setColumn('mempool_descr'),
        ];
    }

    public static function matches(): array
    {
        return [
            'category' => MatchFilter::make()->setType('text')->setColumn('mempool_type'),
            'class' => MatchFilter::make()->setType('text')->setColumn('mempool_class'),
            'description' => MatchFilter::make()->setType('text')->setColumn('mempool_descr'),
            'percentage' => MatchFilter::make()->setType('integer')->setColumn('mempool_perc'),
            'used' => MatchFilter::make()->setType('integer')->setColumn('mempool_used'),
            'free' => MatchFilter::make()->setType('integer')->setColumn('mempool_free'),
            'total' => MatchFilter::make()->setType('integer')->setColumn('mempool_total'),
            'warningPercentage' => MatchFilter::make()->setType('integer')->setColumn('mempool_perc_warn'),
            'index' => MatchFilter::make()->setType('integer')->setColumn('mempool_index'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'category' => SortableFilter::make()->setColumn('mempool_type'),
            'class' => SortableFilter::make()->setColumn('mempool_class'),
            'description' => SortableFilter::make()->setColumn('mempool_descr'),
            'percentage' => SortableFilter::make()->setColumn('mempool_perc'),
            'used' => SortableFilter::make()->setColumn('mempool_used'),
            'free' => SortableFilter::make()->setColumn('mempool_free'),
            'total' => SortableFilter::make()->setColumn('mempool_total'),
            'warningPercentage' => SortableFilter::make()->setColumn('mempool_perc_warn'),
            'index' => SortableFilter::make()->setColumn('mempool_index'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('category', fn ($value, $model) => $model->mempool_type)->readonly(),
            field('class', fn ($value, $model) => $model->mempool_class)->readonly(),
            field('description', fn ($value, $model) => $model->mempool_descr)->readonly(),
            field('percentage', fn ($value, $model) => $model->mempool_perc)->readonly(),
            field('used', fn ($value, $model) => $model->mempool_used)->readonly(),
            field('free', fn ($value, $model) => $model->mempool_free)->readonly(),
            field('total', fn ($value, $model) => $model->mempool_total)->readonly(),
            field('warningPercentage', fn ($value, $model) => $model->mempool_perc_warn)->readonly(),
            field('index', fn ($value, $model) => $model->mempool_index)->readonly(),
        ];
    }

    /**
     * Memory pools are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Memory pools are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
