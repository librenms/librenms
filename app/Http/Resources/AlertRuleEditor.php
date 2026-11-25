<?php

namespace App\Http\Resources;

use App\Facades\LibrenmsConfig;
use App\Models\AlertRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AlertRule */
class AlertRuleEditor extends JsonResource
{
    /**
     * @param  array{name: string, builder: array<string, mixed>, extra: array<string, mixed>, notes?: string, severity?: string}  $definition
     * @return array<string, mixed>
     */
    public static function templateDraft(int $id, array $definition): array
    {
        return [
            'template_id' => $id,
            'name' => $definition['name'],
            'notes' => $definition['notes'] ?? null,
            'builder' => $definition['builder'],
            'extra' => self::extraWithDefaults($definition['extra']),
            'severity' => $definition['severity'] ?? LibrenmsConfig::get('alert_rule.severity'),
            'invert_map' => LibrenmsConfig::get('alert_rule.invert_map'),
            'alert_operation_id' => null,
            'operations' => [],
            'default_operation_step_duration_seconds' => max(0, 60 * (int) LibrenmsConfig::get('alert_rule.default_operation_step_duration', LibrenmsConfig::get('alert_rule.interval'))),
        ];
    }

    /** @return array<string, mixed> */
    public static function copyDraft(AlertRule $rule): array
    {
        return [
            'name' => $rule->name . ' - Copy',
            'extra' => self::extraWithDefaults($rule->extra ?? []),
            'severity' => $rule->severity ?: LibrenmsConfig::get('alert_rule.severity'),
        ] + self::make($rule)->resolve();
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private static function extraWithDefaults(array $extra): array
    {
        return $extra + [
            'invert' => LibrenmsConfig::get('alert_rule.invert_rule_match'),
            'recovery' => LibrenmsConfig::get('alert_rule.recovery_alerts'),
            'acknowledgement' => LibrenmsConfig::get('alert_rule.acknowledgement_alerts'),
        ];
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $this->loadMissing('alertOperation:id,name,default_operation_step_duration_seconds');

        return [
            'extra' => $this->extra,
            'alert_operation_id' => $this->alert_operation_id,
            'alert_operation_name' => $this->alertOperation?->name,
            'operations' => $this->toOperationsApiArray(),
            'default_operation_step_duration_seconds' => $this->alertOperation?->default_operation_step_duration_seconds,
            'name' => $this->name,
            'proc' => $this->proc,
            'notes' => $this->notes,
            'builder' => $this->builder,
            'severity' => $this->severity,
            'adv_query' => $this->query,
            'invert_map' => $this->invert_map,
            'notify_per_entity' => $this->notify_per_entity,
            'max_entities' => $this->max_entities,
        ];
    }
}
