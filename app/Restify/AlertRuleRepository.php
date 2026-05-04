<?php

namespace App\Restify;

use App\Models\AlertRule;
use Binaryk\LaravelRestify\Fields\BelongsTo;
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
            'device-groups' => tap(
                BelongsToMany::make('device-groups', DeviceGroupRepository::class),
                static fn ($f) => $f->relation = 'groups',
            ),
            'locations' => BelongsToMany::make('locations', LocationRepository::class),
            'templates' => BelongsToMany::make('templates', AlertTemplateRepository::class),
            'alertOperation' => BelongsTo::make('alertOperation', AlertOperationRepository::class),
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
            'alertOperationId' => MatchFilter::make()->setType('integer')->setColumn('alert_operation_id'),
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
            'alertOperationId' => SortableFilter::make()->setColumn('alert_operation_id'),
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
            field('query')->rules('nullable', 'string'),
            field('builder')->rules('nullable'),
            field('extra')->rules('nullable'),
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
            field('alertOperationId', fn ($value, $model) => $model->alert_operation_id)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->alert_operation_id = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'integer', 'exists:alert_operations,id'),
        ];
    }
}
