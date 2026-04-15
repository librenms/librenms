<?php

namespace App\Restify;

use App\Models\ServiceTemplate;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class ServiceTemplateRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = ServiceTemplate::class;

    public static string $title = 'name';




    public static function related(): array
    {
        return [
            'devices' => BelongsToMany::make('devices', DeviceRepository::class),
            'groups' => BelongsToMany::make('groups', DeviceGroupRepository::class),
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
            'check' => MatchFilter::make()->setType('text')->setColumn('check'),
            'category' => MatchFilter::make()->setType('text')->setColumn('type'),
            'description' => MatchFilter::make()->setType('text')->setColumn('desc'),
            'parameter' => MatchFilter::make()->setType('text')->setColumn('param'),
            'ip' => MatchFilter::make()->setType('text')->setColumn('ip'),
            'isEnabled' => MatchFilter::make()->setType('bool')->setColumn('disabled'),
            'rules' => MatchFilter::make()->setType('text')->setColumn('rules'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'name' => SortableFilter::make()->setColumn('name'),
            'check' => SortableFilter::make()->setColumn('check'),
            'category' => SortableFilter::make()->setColumn('type'),
            'description' => SortableFilter::make()->setColumn('desc'),
            'parameter' => SortableFilter::make()->setColumn('param'),
            'ip' => SortableFilter::make()->setColumn('ip'),
            'isEnabled' => SortableFilter::make()->setColumn('disabled'),
            'rules' => SortableFilter::make()->setColumn('rules'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')->rules('required', 'string'),
            field('check')->rules('required', 'string'),
            field('category', fn ($value, $model) => $model->type)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->type = $request->input($attribute);
                    }
                })
                ->rules('required', 'string', 'in:static,dynamic'),
            field('description', fn ($value, $model) => $model->desc)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->desc = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string'),
            field('parameter', fn ($value, $model) => $model->param)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->param = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string'),
            field('ip')->rules('nullable', 'string'),
            field('isEnabled', fn ($value, $model) => ! $model->disabled)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->disabled = ! $request->boolean($attribute);
                    }
                })
                ->rules('boolean'),
            field('rules')->readonly(),
        ];
    }
}
