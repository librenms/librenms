<?php

namespace App\Restify;

use App\Models\PortGroup;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class PortGroupRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortGroup::class;

    public static string $title = 'name';




    public static function related(): array
    {
        return [
            'ports' => BelongsToMany::make('ports', PortRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('name'),
        ];
    }

    public static function matches(): array
    {
        return [
            'name' => MatchFilter::make()->setType('text')->setColumn('name'),
            'description' => MatchFilter::make()->setType('text')->setColumn('desc'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'name' => SortableFilter::make()->setColumn('name'),
            'description' => SortableFilter::make()->setColumn('desc'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')->rules('required', 'string', 'max:255'),
            field('description', fn ($value, $model) => $model->desc)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->desc = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string'),
        ];
    }
}
