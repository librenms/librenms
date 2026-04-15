<?php

namespace App\Restify;

use App\Models\Processor;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class ProcessorRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Processor::class;

    public static string $id = 'processor_id';

    public static string $title = 'processor_descr';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'description' => SearchableFilter::make()->setColumn('processor_descr'),
        ];
    }

    public static function matches(): array
    {
        return [
            'category' => MatchFilter::make()->setType('text')->setColumn('processor_type'),
            'description' => MatchFilter::make()->setType('text')->setColumn('processor_descr'),
            'usage' => MatchFilter::make()->setType('integer')->setColumn('processor_usage'),
            'oid' => MatchFilter::make()->setType('text')->setColumn('processor_oid'),
            'index' => MatchFilter::make()->setType('integer')->setColumn('processor_index'),
            'precision' => MatchFilter::make()->setType('integer')->setColumn('processor_precision'),
            'warningPercentage' => MatchFilter::make()->setType('integer')->setColumn('processor_perc_warn'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'category' => SortableFilter::make()->setColumn('processor_type'),
            'description' => SortableFilter::make()->setColumn('processor_descr'),
            'usage' => SortableFilter::make()->setColumn('processor_usage'),
            'oid' => SortableFilter::make()->setColumn('processor_oid'),
            'index' => SortableFilter::make()->setColumn('processor_index'),
            'precision' => SortableFilter::make()->setColumn('processor_precision'),
            'warningPercentage' => SortableFilter::make()->setColumn('processor_perc_warn'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('category', fn ($value, $model) => $model->processor_type)->readonly(),
            field('description', fn ($value, $model) => $model->processor_descr)->readonly(),
            field('usage', fn ($value, $model) => $model->processor_usage)->readonly(),
            field('oid', fn ($value, $model) => $model->processor_oid)->readonly(),
            field('index', fn ($value, $model) => $model->processor_index)->readonly(),
            field('precision', fn ($value, $model) => $model->processor_precision)->readonly(),
            field('warningPercentage', fn ($value, $model) => $model->processor_perc_warn)->readonly(),
        ];
    }

    /**
     * Processors are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Processors are managed by the LibreNMS discovery process — they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
