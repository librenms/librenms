<?php

/**
 * AlertRuleRequest.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Requests;

use App\Models\AlertOperation;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use LibreNMS\Alerting\AlertRuleDefinitions;
use LibreNMS\Alerting\QueryBuilderParser;

class AlertRuleRequest extends FormRequest
{
    /** @var array<string, mixed> */
    private array $builder = [];
    private ?string $ruleQuery = null;
    /** @var array<string, mixed> */
    private array $templateExtra = [];
    /** @var array{devices: list<int>, groups: list<int>, locations: list<int>} */
    private array $maps = ['devices' => [], 'groups' => [], 'locations' => []];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $rule = $this->route('alert_rule');

        return $rule instanceof AlertRule
            ? $this->user()->can('update', $rule)
            : $this->user()->can('create', AlertRule::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::when($this->route()->getActionMethod() === 'store', Rule::unique((app()->environment('testing') ? 'testing.' : '') . 'alert_rules', 'name'))],
            'severity' => ['required', 'string', 'max:32'],

            'override_query' => ['sometimes', 'boolean'],
            'builder_json' => ['required_unless:override_query,true', 'json'],
            'adv_query' => ['required_if:override_query,true', 'nullable', 'string'],
            'template_id' => ['nullable', 'integer', 'min:0'],
            'device' => ['nullable', 'integer', 'min:1'],

            'invert' => ['sometimes', 'boolean'],
            'recovery' => ['sometimes', 'boolean'],
            'mute' => ['sometimes', 'boolean'],
            'acknowledgement' => ['sometimes', 'boolean'],
            'invert_map' => ['sometimes', 'boolean'],
            'notify_per_entity' => ['sometimes', 'boolean'],
            'max_entities' => ['sometimes', 'nullable', 'integer', 'min:1'],

            'maps' => ['sometimes', 'array'],
            'maps.*' => ['string'],

            'alert_operation_id' => ['nullable', 'integer', Rule::exists(AlertOperation::class, 'id')],

            'proc' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Sanitize/normalize inputs before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => isset($this->name) ? strip_tags((string) $this->name) : $this->name,
            'notes' => isset($this->notes) ? strip_tags((string) $this->notes) : $this->notes,
        ]);

        // Convert checkbox values ('on' string) to boolean
        $this->merge(collect(['invert', 'recovery', 'mute', 'acknowledgement', 'invert_map', 'notify_per_entity', 'override_query'])
            ->mapWithKeys(fn ($field) => [$field => match ($this->input($field)) {
                'on', '1', 1, true => true,
                default => false
            }])->all());

        // Ensure maps is array if present as empty string
        foreach (['maps'] as $key) {
            $value = $this->input($key);
            if ($value === '' || $value === null) {
                $this->merge([$key => []]);
            }
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $this->validateBuilder($v);
            $this->validateMaps($v);

            $templateId = $this->input('template_id');
            if ($templateId !== null) {
                $template = AlertRuleDefinitions::all()[(int) $templateId] ?? null;
                if ($template === null) {
                    $v->errors()->add('template_id', __('The selected alert rule template does not exist'));
                } else {
                    $this->templateExtra = Arr::only($template['extra'], ['count', 'delay', 'interval']);
                }
            }
        });
    }

    /** @return array<model-property<AlertRule>, mixed> */
    public function ruleAttributes(): array
    {
        return $this->safe(['severity', 'name', 'proc', 'notes', 'invert_map', 'notify_per_entity', 'max_entities', 'alert_operation_id']) + [
            'builder' => $this->builder,
            'query' => $this->ruleQuery,
            'extra' => $this->templateExtra + $this->safe(['invert', 'acknowledgement', 'recovery', 'mute']) + [
                'options' => ['override_query' => $this->boolean('override_query')],
            ],
            'alert_operation_id' => null,
        ];
    }

    /** @return array{devices: list<int>, groups: list<int>, locations: list<int>} */
    public function mappingIds(): array
    {
        return $this->maps;
    }

    private function validateBuilder(Validator $validator): void
    {
        $builder = json_decode((string) $this->input('builder_json', '[]'), true);
        if (is_array($builder) && QueryBuilderParser::fromJson($builder)->isValid()) {
            $this->builder = $builder;
        }

        if ($this->boolean('override_query')) {
            $this->ruleQuery = $this->input('adv_query');

            return;
        }

        try {
            $this->ruleQuery = QueryBuilderParser::fromJson($this->builder)->toSql();
        } catch (\Exception | \TypeError) {
            $this->ruleQuery = null;
        }

        if ($this->ruleQuery === null) {
            $validator->errors()->add('builder_json', __('The alert rule builder must contain valid rules'));
        }
    }

    private function validateMaps(Validator $validator): void
    {
        $maps = $this->input('maps', []);
        if ($this->boolean('invert_map') && $maps === []) {
            $validator->errors()->add('maps', __('Invert map is on but no selection in devices, groups and locations match list'));
        }

        foreach ($maps as $key => $target) {
            if (! preg_match('/\A([gl]?)([1-9][0-9]*)\z/', $target, $match) || filter_var($match[2], FILTER_VALIDATE_INT) === false) {
                $validator->errors()->add("maps.$key", __('The selected mapping is invalid'));

                continue;
            }

            $type = match ($match[1]) {
                'g' => 'groups',
                'l' => 'locations',
                default => 'devices',
            };
            $this->maps[$type][] = (int) $match[2];
        }

        foreach (['devices' => Device::class, 'groups' => DeviceGroup::class, 'locations' => Location::class] as $type => $model) {
            $ids = array_values(array_unique($this->maps[$type]));
            if ($ids !== [] && $model::whereKey($ids)->count() !== count($ids)) {
                $validator->errors()->add('maps', __('A selected device, group or location no longer exists'));
            }
        }
    }
}
