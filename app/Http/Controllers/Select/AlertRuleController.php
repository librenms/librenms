<?php

namespace App\Http\Controllers\Select;

use App\Models\Alert;
use App\Models\AlertLog;
use App\Models\AlertRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @extends SelectController<AlertRule>
 */
class AlertRuleController extends SelectController
{
    /**
     * @return list<string>
     */
    protected function searchFields(Request $request): array
    {
        return ['alert_rules.name'];
    }

    /**
     * @return Builder<AlertRule>
     */
    public function baseQuery(Request $request): Builder
    {
        // used by both the faults and alert history filters
        abort_unless($request->user()->can('viewAny', Alert::class) || $request->user()->can('viewAny', AlertLog::class), 403);

        // rule names often contain customer or site names, only list rules on devices the user can access
        return AlertRule::query()
            ->hasAccess($request->user())
            ->select(['alert_rules.id', 'alert_rules.name'])
            ->distinct()
            ->orderBy('alert_rules.name');
    }

    /**
     * @param  AlertRule  $model
     * @return array{id: int|string, text: string, icon?: string}
     */
    public function formatItem(Model $model): array
    {
        return [
            'id' => $model->id,
            'text' => (string) $model->name,
        ];
    }
}
