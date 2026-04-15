<?php

namespace App\Restify;

use App\Models\AlertRule;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class AlertRuleRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = AlertRule::class;

    public static string $title = 'name';




    public static function related(): array
    {
        return [
            'devices' => BelongsToMany::make('devices', DeviceRepository::class),
            'groups' => BelongsToMany::make('groups', DeviceGroupRepository::class),
            'locations' => BelongsToMany::make('locations', LocationRepository::class),
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
            'severity' => MatchFilter::make()->setType('text')->setColumn('severity'),
            'isEnabled' => MatchFilter::make()->setType('bool')->setColumn('disabled'),
            'rule' => MatchFilter::make()->setType('text')->setColumn('rule'),
            'query' => MatchFilter::make()->setType('text')->setColumn('query'),
            'builder' => MatchFilter::make()->setType('text')->setColumn('builder'),
            'extra' => MatchFilter::make()->setType('text')->setColumn('extra'),
            'procedure' => MatchFilter::make()->setType('text')->setColumn('proc'),
            'notes' => MatchFilter::make()->setType('text')->setColumn('notes'),
            'isInverted' => MatchFilter::make()->setType('bool')->setColumn('invert_map'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'name' => SortableFilter::make()->setColumn('name'),
            'severity' => SortableFilter::make()->setColumn('severity'),
            'isEnabled' => SortableFilter::make()->setColumn('disabled'),
            'rule' => SortableFilter::make()->setColumn('rule'),
            'query' => SortableFilter::make()->setColumn('query'),
            'builder' => SortableFilter::make()->setColumn('builder'),
            'extra' => SortableFilter::make()->setColumn('extra'),
            'procedure' => SortableFilter::make()->setColumn('proc'),
            'notes' => SortableFilter::make()->setColumn('notes'),
            'isInverted' => SortableFilter::make()->setColumn('invert_map'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')->rules('required', 'string', 'max:255'),
            field('severity')->rules('required', 'in:ok,warning,critical'),
            field('isEnabled', fn ($value, $model) => ! $model->disabled)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->disabled = ! $request->boolean($attribute);
                    }
                })
                ->rules('required', 'boolean'),
            field('rule')->readonly(),
            field('query')->readonly(),
            field('builder')->readonly(),
            field('extra')->readonly(),
            field('procedure', fn ($value, $model) => $model->proc)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->proc = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string', 'max:80'),
            field('notes')->rules('nullable', 'string'),
            field('isInverted', fn ($value, $model) => $model->invert_map)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->invert_map = $request->input($attribute);
                    }
                })
                ->rules('boolean'),
        ];
    }
}
