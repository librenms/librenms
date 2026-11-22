<?php

namespace App\Actions\Alerts;

use App\Models\Alert;
use App\Models\AlertFault;
use App\Models\AlertRule;
use LibreNMS\Enum\AlertState;

/**
 * Close the open faults and clear the alerts of a rule that will no longer be evaluated.
 */
final class ClearAlertRuleAlerts
{
    public function execute(AlertRule $rule): void
    {
        AlertFault::where('rule_id', $rule->id)->where('open', 1)
            ->update(['open' => 0, 'state' => AlertState::RECOVERED]);

        Alert::where('rule_id', $rule->id)->get()->each(function (Alert $alert): void {
            $info = is_array($alert->info) ? $alert->info : [];
            unset($info['open_fault_count']);
            $alert->info = $info;
            $alert->update([
                'state' => AlertState::CLEAR,
                'open' => 0,
                'alerted' => 0,
            ]);
        });
    }
}
