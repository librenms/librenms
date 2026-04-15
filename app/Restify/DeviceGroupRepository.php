<?php

namespace App\Restify;

use App\Models\DeviceGroup;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class DeviceGroupRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = DeviceGroup::class;

    public static string $title = 'name';




    public static function related(): array
    {
        return [
            'devices' => BelongsToMany::make('devices', DeviceRepository::class),
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
            'category' => MatchFilter::make()->setType('text')->setColumn('type'),
            'rules' => MatchFilter::make()->setType('text')->setColumn('rules'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'name' => SortableFilter::make()->setColumn('name'),
            'description' => SortableFilter::make()->setColumn('desc'),
            'category' => SortableFilter::make()->setColumn('type'),
            'rules' => SortableFilter::make()->setColumn('rules'),
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
                ->rules('nullable', 'string', 'max:255'),
            field('category', fn ($value, $model) => $model->type)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->type = $request->input($attribute);
                    }
                })
                ->rules('required', 'string', 'in:dynamic,static'),
            field('rules')->readonly(),
        ];
    }
}
