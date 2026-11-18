<?php

namespace App\Http\Controllers\Select;

use App\Models\Alert;
use App\Models\AlertLog;
use App\Models\AlertRule;
use App\Models\Device;
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
        $user = $request->user();
        // used by both the faults and alert history filters
        abort_unless($user->can('viewAny', Alert::class) || $user->can('viewAny', AlertLog::class), 403);

        // rule names often contain customer or site names, only list rules on devices the user can access
        return AlertRule::query()
            ->when($user->cannot('viewAll', AlertRule::class), fn ($query) => $query->whereHas('alerts', fn ($alerts) => $alerts
                ->whereIn('alerts.device_id', Device::hasAccess($user)->select('devices.device_id'))))
            ->select(['alert_rules.id', 'alert_rules.name'])
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
