<?php

namespace App\Restify;

use App\Actions\Alerts\ClearAlertRuleAlerts;
use App\Facades\LibrenmsConfig;
use App\Models\AlertOperation;
use App\Models\AlertRule;
use Binaryk\LaravelRestify\Fields\Field;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use LibreNMS\Alerting\QueryBuilderParser;

/**
 * @property AlertRule $resource
 */
class AlertRuleRepository extends Repository
{
    public static string $model = AlertRule::class;

    public static string $title = 'name';

    /** @var list<string> */
    public static array $search = ['name'];

    /** @var list<string> */
    public static array $sort = ['name'];

    /**
     * Users with only alert-rule.view see the rules mapped to devices they can access.
     *
     * @param  Builder<AlertRule>|Relation<AlertRule, Model, mixed>  $query
     * @return Builder<AlertRule>|Relation<AlertRule, Model, mixed>
     */
    public static function indexQuery(RestifyRequest $request, Builder|Relation $query): Builder|Relation
    {
        return $query->hasAccess($request->user());
    }

    /**
     * Shown rules are limited like listed ones. Writes are not scoped, the update and delete
     * permissions apply to every rule, as in the web UI.
     *
     * @param  Builder<AlertRule>|Relation<AlertRule, Model, mixed>  $query
     * @return Builder<AlertRule>|Relation<AlertRule, Model, mixed>
     */
    public static function showQuery(RestifyRequest $request, Builder|Relation $query): Builder|Relation
    {
        return $query->hasAccess($request->user());
    }

    /**
     * @return array<string, MatchFilter>
     */
    public static function matches(): array
    {
        return [
            'name' => MatchFilter::make()->setType('text')->setColumn('name'),
            'severity' => MatchFilter::make()->setType('text')->setColumn('severity'),
            'alertOperationId' => MatchFilter::make()->setType('integer')->setColumn('alert_operation_id'),
        ];
    }

    /**
     * @return array<int, Field>
     */
    public function fields(RestifyRequest $request): array
    {
        return [
            field('name')
                ->rules('required', 'string', $this->validateStrippedName(...))
                ->fillCallback(function (RestifyRequest $request, AlertRule $rule, string $attribute): void {
                    if ($request->exists($attribute)) {
                        $rule->name = strip_tags((string) $request->input($attribute));
                    }
                }),
            field('severity')->rules('required', Rule::in(['ok', 'warning', 'critical'])),
            $this->column('isEnabled', 'disabled', fn (AlertRule $rule): bool => ! $rule->disabled, fn (mixed $value): bool => ! filter_var($value, FILTER_VALIDATE_BOOLEAN))
                ->rules('boolean')
                ->afterUpdate(function (mixed $value, mixed $oldValue, AlertRule $rule): void {
                    if ($rule->wasChanged('disabled') && $rule->disabled) {
                        app(ClearAlertRuleAlerts::class)->execute($rule);
                    }
                }),
            field('builder')
                ->rules([Rule::requiredIf(fn (): bool => ! $this->overridesQuery($request)), 'array', function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value !== [] && (! is_array($value) || ! QueryBuilderParser::fromJson($value)->isValid())) {
                        $fail(__('The :attribute must be a group of complete rules.'));
                    }
                }]),
            field('query')
                ->rules([Rule::requiredIf(fn (): bool => $this->overridesQuery($request)), 'nullable', 'string'])
                ->fillCallback(function (RestifyRequest $request, AlertRule $rule, string $attribute): void {
                    if ($request->exists($attribute)) {
                        $rule->query = (string) $request->input($attribute);
                    }
                }),
            $this->extraFlag('overridesQuery', 'options.override_query', false),
            $this->extraFlag('isConditionInverted', 'invert', false),
            $this->extraFlag('isMuted', 'mute', false),
            $this->extraFlag('sendsRecoveryAlerts', 'recovery', true),
            $this->extraFlag('sendsAcknowledgementAlerts', 'acknowledgement', true),
            $this->column('notifiesPerEntity', 'notify_per_entity', fn (AlertRule $rule): bool => (bool) $rule->notify_per_entity, fn (mixed $value): bool => filter_var($value, FILTER_VALIDATE_BOOLEAN))
                ->rules('boolean'),
            $this->column('maximumEntities', 'max_entities', fn (AlertRule $rule): ?int => $rule->max_entities)
                ->rules('nullable', 'integer', 'min:1', 'max:4294967295'),
            $this->column('procedure', 'proc', fn (AlertRule $rule): ?string => $rule->proc)
                ->rules('nullable', 'string', 'max:80'),
            field('notes')
                ->rules('nullable', 'string')
                ->fillCallback(function (RestifyRequest $request, AlertRule $rule, string $attribute): void {
                    if ($request->exists($attribute)) {
                        $notes = $request->input($attribute);
                        $rule->notes = $notes === null ? null : strip_tags((string) $notes);
                    }
                }),
            field('isScopeInverted', fn (mixed $value, self $repository): bool => (bool) $repository->resource->invert_map)
                ->readonly(),
            $this->column('alertOperationId', 'alert_operation_id', fn (AlertRule $rule): ?int => $rule->alert_operation_id)
                ->rules('nullable', 'integer', Rule::exists(AlertOperation::class, 'id')),
        ];
    }

    /**
     * New rules get the configured defaults, as in the web UI, and the query is generated
     * from the builder unless the rule overrides it. A query sent for a rule that does not
     * override it is replaced.
     *
     * @param  AlertRule  $model
     * @param  Collection<int, Field>  $fields
     * @return Collection<int, mixed>
     *
     * @throws ValidationException
     */
    protected static function fillFields(RestifyRequest $request, Model $model, Collection $fields): Collection
    {
        $filled = parent::fillFields($request, $model, $fields);

        $model->disabled ??= false;
        $model->builder ??= [];
        if (! $model->exists) {
            $model->extra = ($model->extra ?? []) + [
                'invert' => (bool) LibrenmsConfig::get('alert_rule.invert_rule_match'),
                'recovery' => (bool) LibrenmsConfig::get('alert_rule.recovery_alerts'),
                'acknowledgement' => (bool) LibrenmsConfig::get('alert_rule.acknowledgement_alerts'),
            ];
        }

        if (! empty($model->extra['options']['override_query'])) {
            if (empty($model->query)) {
                throw ValidationException::withMessages(['query' => __('The query field is required when overridesQuery is true.')]);
            }
        } elseif (! $model->exists || $request->hasAny(['builder', 'overridesQuery', 'query'])) {
            $model->query = self::builderQuery($model->builder);
        }

        return $filled;
    }

    /**
     * @param  array<mixed>  $builder
     *
     * @throws ValidationException
     */
    private static function builderQuery(array $builder): string
    {
        try {
            $parser = QueryBuilderParser::fromJson($builder);
            $query = $parser->isValid() ? $parser->toSql() : null;
        } catch (\Exception|\TypeError) {
            $query = null;
        }

        if (empty($query)) {
            throw ValidationException::withMessages(['builder' => __('A builder that is a group of complete rules is required to generate the query.')]);
        }

        return $query;
    }

    /**
     * The override sent with the request, or the one stored on the rule.
     */
    private function overridesQuery(RestifyRequest $request): bool
    {
        return $request->has('overridesQuery')
            ? $request->boolean('overridesQuery')
            : (bool) data_get($this->resource->extra, 'options.override_query', false);
    }

    /**
     * Tags are stripped from the name before it is saved, so check the stored form.
     */
    private function validateStrippedName(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $name = strip_tags($value);
        if (trim($name) === '') {
            $fail(__('validation.required', ['attribute' => $attribute]));
        } elseif (mb_strlen($name) > 255) {
            $fail(__('validation.max.string', ['attribute' => $attribute, 'max' => 255]));
        } elseif (AlertRule::where('name', $name)->whereKeyNot($this->resource->getKey())->exists()) {
            $fail(__('validation.unique', ['attribute' => $attribute]));
        }
    }

    /**
     * A field stored in a differently named column.
     *
     * @param  Closure(AlertRule): mixed  $toApi
     * @param  (Closure(mixed): mixed)|null  $toModel
     */
    private function column(string $attribute, string $column, Closure $toApi, ?Closure $toModel = null): Field
    {
        return field($attribute, fn (mixed $value, self $repository): mixed => $toApi($repository->resource))
            ->fillCallback(function (RestifyRequest $request, AlertRule $rule, string $attribute) use ($column, $toModel): void {
                if ($request->exists($attribute)) {
                    $value = $request->input($attribute);
                    $rule->{$column} = $toModel ? $toModel($value) : $value;
                }
            });
    }

    /**
     * A boolean kept in the extra JSON column.
     *
     * @param  string  $key  dot notation key within extra
     */
    private function extraFlag(string $attribute, string $key, bool $default): Field
    {
        return field($attribute, fn (mixed $value, self $repository): bool => (bool) data_get($repository->resource->extra, $key, $default))
            ->rules('boolean')
            ->fillCallback(function (RestifyRequest $request, AlertRule $rule, string $attribute) use ($key): void {
                if ($request->exists($attribute)) {
                    $extra = $rule->extra ?? [];
                    data_set($extra, $key, $request->boolean($attribute));
                    $rule->extra = $extra;
                }
            });
    }
}
