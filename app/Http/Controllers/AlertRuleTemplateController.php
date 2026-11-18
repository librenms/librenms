<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Models\AlertRule;

class AlertRuleTemplateController extends Controller
{
    public function template(int $template_id)
    {
        $this->authorize('create', AlertRule::class);

        $collection = $this->templatesCollection();

        if (! isset($collection[$template_id])) {
            return response()->json(['status' => 'error', 'message' => 'Template not found'], 404);
        }

        $rule = $collection[$template_id];

        return response()->json([
            'status' => 'ok',
            'name' => $rule['name'],
            'notes' => $rule['notes'] ?? null,
            'builder' => $rule['builder'] ?? [],
            'extra' => $this->extraWithDefaults((array) ($rule['extra'] ?? [])),
            'severity' => $rule['severity'] ?? LibrenmsConfig::get('alert_rule.severity'),
            'invert_map' => LibrenmsConfig::get('alert_rule.invert_map'),
            'alert_operation_id' => null,
            'operations' => [],
            'default_operation_step_duration_seconds' => max(0, 60 * (int) LibrenmsConfig::get('alert_rule.default_operation_step_duration', LibrenmsConfig::get('alert_rule.interval'))),
        ]);
    }

    public function rule(AlertRule $alertRule)
    {
        $this->authorize('create', AlertRule::class);
        $this->authorize('view', $alertRule);

        $alertRule->loadMissing('alertOperation:id,name,default_operation_step_duration_seconds');

        return response()->json([
            'status' => 'ok',
            'name' => $alertRule->name . ' - Copy',
            'proc' => $alertRule->proc,
            'notes' => $alertRule->notes,
            'builder' => $alertRule->builder,
            'extra' => $this->extraWithDefaults((array) $alertRule->extra),
            'severity' => $alertRule->severity ?: LibrenmsConfig::get('alert_rule.severity'),
            'adv_query' => $alertRule->query,
            'invert_map' => $alertRule->invert_map,
            'notify_per_entity' => $alertRule->notify_per_entity,
            'max_entities' => $alertRule->max_entities,
            'alert_operation_id' => $alertRule->alert_operation_id,
            'alert_operation_name' => $alertRule->alertOperation?->name,
            'operations' => $alertRule->toOperationsApiArray(),
            'default_operation_step_duration_seconds' => $alertRule->alertOperation?->default_operation_step_duration_seconds,
        ]);
    }

    private function extraWithDefaults(array $extra): array
    {
        $default_extra = [
            'invert' => LibrenmsConfig::get('alert_rule.invert_rule_match'),
            'recovery' => LibrenmsConfig::get('alert_rule.recovery_alerts'),
            'acknowledgement' => LibrenmsConfig::get('alert_rule.acknowledgement_alerts'),
        ];

        return array_replace($default_extra, $extra);
    }

    public function templatesCollection(): array
    {
        return json_decode(file_get_contents(resource_path('definitions/alert_rules.json')), true);
    }
}
