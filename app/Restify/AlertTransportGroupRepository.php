<?php

namespace App\Restify;

use App\Models\AlertTransportGroup;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;

class AlertTransportGroupRepository extends Repository
{
    public static string $model = AlertTransportGroup::class;

    public static string $id = 'transport_group_id';

    public static string $title = 'transport_group_name';

    public static function related(): array
    {
        return [
            'transports' => BelongsToMany::make('transports', AlertTransportRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('transport_group_name'),
        ];
    }

    public static function matches(): array
    {
        return [
            'name' => MatchFilter::make()->setType('text')->setColumn('transport_group_name'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'name' => SortableFilter::make()->setColumn('transport_group_name'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name', fn ($value, $model) => $model->transport_group_name)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->transport_group_name = $request->input($attribute);
                    }
                })
                ->rules('required', 'string', 'max:255'),
        ];
    }
}
