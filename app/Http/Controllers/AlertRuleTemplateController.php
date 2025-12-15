<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Models\AlertRule;
use Illuminate\Http\JsonResponse;

class AlertRuleTemplateController extends Controller
{
    public function index(): JsonResponse
    {
        $collection = self::templatesCollection();

        return response()->json(collect($collection)->map(fn ($rule, $index) => [
            'id' => $index,
            'name' => $rule['name'],
            'builder' => $rule['builder'] ?? null,
        ])->values()->all());
    }

    public function show(int $template_id): JsonResponse
    {
        $collection = self::templatesCollection();

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
        ]);
    }

    public function rule(AlertRule $alertRule): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'name' => $alertRule->name . ' - Copy',
            'builder' => $alertRule->builder,
            'extra' => $this->extraWithDefaults((array) $alertRule->extra),
            'severity' => $alertRule->severity ?: LibrenmsConfig::get('alert_rule.severity'),
            'invert_map' => $alertRule->invert_map,
        ]);
    }

    private function extraWithDefaults(array $extra): array
    {
        $default_extra = [
            'mute' => LibrenmsConfig::get('alert_rule.mute_alerts'),
            'count' => LibrenmsConfig::get('alert_rule.max_alerts'),
            'delay' => 60 * LibrenmsConfig::get('alert_rule.delay'),
            'invert' => LibrenmsConfig::get('alert_rule.invert_rule_match'),
            'interval' => 60 * LibrenmsConfig::get('alert_rule.interval'),
            'recovery' => LibrenmsConfig::get('alert_rule.recovery_alerts'),
            'acknowledgement' => LibrenmsConfig::get('alert_rule.acknowledgement_alerts'),
        ];

        return array_replace($default_extra, $extra);
    }

    public static function templatesCollection(): array
    {
        return json_decode(file_get_contents(resource_path('definitions/alert_rules.json')), true);
    }
}
