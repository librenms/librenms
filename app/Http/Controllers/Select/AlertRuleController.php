<?php

namespace App\Http\Controllers\Select;

use App\Models\Alert;
use App\Models\AlertFault;
use App\Models\AlertLog;
use App\Models\AlertRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LibreNMS\Enum\AlertState;

/**
 * @extends SelectController<AlertRule>
 */
class AlertRuleController extends SelectController
{
    protected function rules(): array
    {
        return [
            'source' => 'sometimes|in:faults,history',
            'device' => 'sometimes|integer|min:1',
        ];
    }

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
        $history = $request->input('source') === 'history';
        $this->authorize('viewAny', $history ? AlertLog::class : Alert::class);

        $rows = $history ? AlertLog::hasAccess($user) : AlertFault::hasAccess($user)
            ->open()
            ->onEnabledDevicesAndRules()
            ->where('state', '!=', AlertState::RECOVERED);
        if ($deviceId = $request->integer('device')) {
            $rows->where('device_id', $deviceId);
        }

        return AlertRule::query()
            ->whereExists($rows->selectRaw('1')->whereColumn('rule_id', 'alert_rules.id')->toBase())
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
