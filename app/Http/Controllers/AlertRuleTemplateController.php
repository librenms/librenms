<?php

namespace App\Http\Controllers;

use App\Http\Resources\AlertRuleEditor;
use App\Models\AlertRule;
use Illuminate\Http\JsonResponse;
use LibreNMS\Alerting\AlertRuleDefinitions;

class AlertRuleTemplateController extends Controller
{
    public function template(int $template_id): JsonResponse
    {
        $this->authorize('create', AlertRule::class);

        $collection = AlertRuleDefinitions::all();

        if (! isset($collection[$template_id])) {
            return response()->json(['status' => 'error', 'message' => 'Template not found'], 404);
        }

        return response()->json(['status' => 'ok'] + AlertRuleEditor::templateDraft($template_id, $collection[$template_id]));
    }

    public function rule(AlertRule $alertRule): JsonResponse
    {
        $this->authorize('create', AlertRule::class);
        $this->authorize('view', $alertRule);

        return response()->json(['status' => 'ok'] + AlertRuleEditor::copyDraft($alertRule));
    }
}
