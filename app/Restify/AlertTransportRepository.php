<?php

namespace App\Restify;

use App\Models\AlertTransport;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class AlertTransportRepository extends Repository
{
    public static string $model = AlertTransport::class;

    public static string $title = 'transport_name';




    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('transport_name'),
        ];
    }

    public static function matches(): array
    {
        return [
            'name' => MatchFilter::make()->setType('text')->setColumn('transport_name'),
            'category' => MatchFilter::make()->setType('text')->setColumn('transport_type'),
            'isDefault' => MatchFilter::make()->setType('bool')->setColumn('is_default'),
            'configuration' => MatchFilter::make()->setType('text')->setColumn('transport_config'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'name' => SortableFilter::make()->setColumn('transport_name'),
            'category' => SortableFilter::make()->setColumn('transport_type'),
            'isDefault' => SortableFilter::make()->setColumn('is_default'),
            'configuration' => SortableFilter::make()->setColumn('transport_config'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name', fn ($value, $model) => $model->transport_name)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->transport_name = $request->input($attribute);
                    }
                })
                ->rules('required', 'string'),
            field('category', fn ($value, $model) => $model->transport_type)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->transport_type = $request->input($attribute);
                    }
                })
                ->rules('required', 'string'),
            field('isDefault', fn ($value, $model) => $model->is_default)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->is_default = $request->input($attribute);
                    }
                })
                ->rules('boolean'),
            field('configuration', fn ($value, $model) => $model->transport_config)->readonly(),
        ];
    }

    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return $query;
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return $query;
    }
}
