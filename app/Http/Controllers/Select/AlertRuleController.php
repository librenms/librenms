<?php

namespace App\Http\Controllers\Select;

use App\Models\AlertRule;

class AlertRuleController extends SelectController
{
    protected function rules()
    {
        return [];
    }

    protected function searchFields($request)
    {
        return ['name'];
    }

    protected function baseQuery($request)
    {
        return AlertRule::query()
            ->select('id', 'name', 'severity')
            ->orderBy('name');
    }

    public function formatItem($alertRule)
    {
        /** @var AlertRule $alertRule */
        return [
            'id' => $alertRule->id,
            'text' => $alertRule->name,
        ];
    }
}
