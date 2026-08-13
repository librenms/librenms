<?php

namespace App\Restify;

use App\Models\AlertRule;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;

class AlertRuleRepository extends Repository
{
    // Not device-scoped: alert-rules are visible per alert-rule permission; device
    // access only matters for the rule's related devices/groups, which are not
    // exposed through this repository.

    public static string $model = AlertRule::class;

    public static string $title = 'name';

    /**
     * @return array<string, SearchableFilter>
     */
    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('name'),
        ];
    }

    /**
     * @return array<string, MatchFilter>
     */
    public static function matches(): array
    {
        return [
            'name' => MatchFilter::make()->setType('text')->setColumn('name'),
            'severity' => MatchFilter::make()->setType('text')->setColumn('severity'),
            'isEnabled' => MatchFilter::make()->setType('bool')->setColumn('disabled'),
            'query' => MatchFilter::make()->setType('text')->setColumn('query'),
            'procedure' => MatchFilter::make()->setType('text')->setColumn('proc'),
            'notes' => MatchFilter::make()->setType('text')->setColumn('notes'),
            'isScopeInverted' => MatchFilter::make()->setType('bool')->setColumn('invert_map'),
            'alertOperationId' => MatchFilter::make()->setType('integer')->setColumn('alert_operation_id'),
        ];
    }

    /**
     * @return array<string, SortableFilter>
     */
    public static function sorts(): array
    {
        return [
            'name' => SortableFilter::make()->setColumn('name'),
            'severity' => SortableFilter::make()->setColumn('severity'),
            'isEnabled' => SortableFilter::make()->setColumn('disabled'),
            'isScopeInverted' => SortableFilter::make()->setColumn('invert_map'),
            'alertOperationId' => SortableFilter::make()->setColumn('alert_operation_id'),
        ];
    }

    /**
     * @return array<int, \Binaryk\LaravelRestify\Fields\Field>
     */
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
            // The flags below live in the extra JSON column; only these keys are still
            // read by the alerting code (notification pacing moved to alert operations),
            // so they are exposed as first-class attributes instead of a raw blob.
            // recovery and acknowledgement are treated as enabled when absent.
            field('isConditionInverted', fn ($value, $model) => (bool) ($model->extra['invert'] ?? false))
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        self::setExtra($model, 'invert', $request->boolean($attribute));
                    }
                })
                ->rules('boolean'),
            field('isMuted', fn ($value, $model) => (bool) ($model->extra['mute'] ?? false))
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        self::setExtra($model, 'mute', $request->boolean($attribute));
                    }
                })
                ->rules('boolean'),
            field('sendsRecoveryAlerts', fn ($value, $model) => (bool) ($model->extra['recovery'] ?? true))
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        self::setExtra($model, 'recovery', $request->boolean($attribute));
                    }
                })
                ->rules('boolean'),
            field('sendsAcknowledgementAlerts', fn ($value, $model) => (bool) ($model->extra['acknowledgement'] ?? true))
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        self::setExtra($model, 'acknowledgement', $request->boolean($attribute));
                    }
                })
                ->rules('boolean'),
            field('overridesQuery', fn ($value, $model) => (bool) ($model->extra['options']['override_query'] ?? false))
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $options = $model->extra['options'] ?? [];
                        $options['override_query'] = $request->boolean($attribute);
                        self::setExtra($model, 'options', $options);
                    }
                })
                ->rules('boolean'),
            field('procedure', fn ($value, $model) => $model->proc)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->proc = $request->input($attribute);
                    }
                })
                ->rules('nullable', 'string', 'max:80'),
            field('notes')->rules('nullable', 'string'),
            field('isScopeInverted', fn ($value, $model) => (bool) $model->invert_map)
                ->fillCallback(function ($request, $model, $attribute) {
                    if ($request->exists($attribute)) {
                        $model->invert_map = $request->boolean($attribute);
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

    /**
     * Write a key into the extra JSON column; the array cast does not allow
     * in-place mutation of nested values.
     */
    private static function setExtra(AlertRule $model, string $key, mixed $value): void
    {
        $extra = $model->extra ?? [];
        $extra[$key] = $value;
        $model->extra = $extra;
    }
}
