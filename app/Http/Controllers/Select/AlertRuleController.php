<?php

namespace App\Http\Controllers\Select;

use App\Models\Alert;
use App\Models\AlertFault;
use App\Models\AlertRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LibreNMS\Enum\AlertState;

/**
 * @extends SelectController<AlertRule>
 */
class AlertRuleController extends SelectController
{
    protected ?string $idField = 'id';
    protected ?string $textField = 'name';

    /** @return array{device: string} */
    protected function rules(): array
    {
        return ['device' => 'sometimes|integer|min:1'];
    }

    /**
     * @return list<string>
     */
    protected function searchFields(Request $request): array
    {
        return ['name'];
    }

    /**
     * @return Builder<AlertRule>
     */
    public function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Alert::class);

        $faults = AlertFault::hasAccess($request->user())
            ->open()
            ->onEnabledDevicesAndRules()
            ->where('state', '!=', AlertState::RECOVERED)
            ->when($request->integer('device'), fn ($query, $deviceId) => $query->where('device_id', $deviceId));

        return AlertRule::query()
            ->whereExists($faults->selectRaw('1')->whereColumn('rule_id', 'alert_rules.id')->toBase())
            ->orderBy('name');
    }
}
