<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Models\AlertRule;
use LibreNMS\Enum\AlertRuleOperationPhase;

class AlertRuleTemplateController extends Controller
{
    public function template(int $template_id)
    {
        $collection = $this->templatesCollection();

        if (! isset($collection[$template_id])) {
            return response()->json(['status' => 'error', 'message' => 'Template not found'], 404);
        }

        $rule = $collection[$template_id];

        $maxAlerts = (int) LibrenmsConfig::get('alert_rule.default_operation_steps_to', LibrenmsConfig::get('alert_rule.max_alerts'));

        return response()->json([
            'status' => 'ok',
            'name' => $rule['name'],
            'notes' => $rule['notes'] ?? null,
            'builder' => $rule['builder'] ?? [],
            'extra' => $this->extraWithDefaults((array) ($rule['extra'] ?? [])),
            'severity' => $rule['severity'] ?? LibrenmsConfig::get('alert_rule.severity'),
            'invert_map' => LibrenmsConfig::get('alert_rule.invert_map'),
            'operations' => [
                [
                    'operation_phase' => AlertRuleOperationPhase::PROBLEM,
                    'escalation_step_from' => 1,
                    'escalation_step_to' => $maxAlerts === -1 ? null : max(1, $maxAlerts),
                    'start_in_seconds' => max(0, 60 * (int) LibrenmsConfig::get('alert_rule.default_operation_start_in', LibrenmsConfig::get('alert_rule.delay'))),
                    'step_duration_seconds' => max(0, 60 * (int) LibrenmsConfig::get('alert_rule.default_operation_step_duration', LibrenmsConfig::get('alert_rule.interval'))),
                    'transports' => [],
                ],
            ],
            'default_operation_step_duration_seconds' => max(0, 60 * (int) LibrenmsConfig::get('alert_rule.default_operation_step_duration', LibrenmsConfig::get('alert_rule.interval'))),
        ]);
    }

    public function rule(AlertRule $alertRule)
    {
        return response()->json([
            'status' => 'ok',
            'name' => $alertRule->name . ' - Copy',
            'builder' => $alertRule->builder,
            'extra' => $this->extraWithDefaults((array) $alertRule->extra),
            'severity' => $alertRule->severity ?: LibrenmsConfig::get('alert_rule.severity'),
            'invert_map' => $alertRule->invert_map,
            'operations' => $alertRule->toOperationsApiArray(),
            'default_operation_step_duration_seconds' => $alertRule->default_operation_step_duration_seconds,
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
