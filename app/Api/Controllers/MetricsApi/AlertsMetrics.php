<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Alert;
use App\Models\AlertRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlertsMetrics
{
    use MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Parse filters
        $filters = $this->parseDeviceFilters($request);

        // Gather global metrics
        $total_rules = AlertRule::count();
        $lines[] = '# HELP librenms_alert_rules_total Total number of alert rules';
        $lines[] = '# TYPE librenms_alert_rules_total gauge';
        $lines[] = "librenms_alert_rules_total {$total_rules}";

        $alertsQ = Alert::query();
        $alertsQ = $this->applyDeviceFilter($alertsQ, $filters['device_ids']);
        $total_alerts = $alertsQ->count();
        $lines[] = '# HELP librenms_alerts_total Total number of alerts rows';
        $lines[] = '# TYPE librenms_alerts_total gauge';
        $lines[] = "librenms_alerts_total {$total_alerts}";

        // Alerts by state
        $lines[] = '# HELP librenms_alerts_by_state Number of alerts by state';
        $lines[] = '# TYPE librenms_alerts_by_state gauge';
        $statesQ = Alert::select('state', DB::raw('count(*) as cnt'))->groupBy('state');
        $statesQ = $this->applyDeviceFilter($statesQ, $filters['device_ids']);
        $states = $statesQ->get();
        foreach ($states as $s) {
            $lines[] = sprintf('librenms_alerts_by_state{state="%s"} %d', $s->state, $s->cnt);
        }

        // Rules by severity
        $lines[] = '# HELP librenms_alert_rules_by_severity Number of alert rules by severity';
        $lines[] = '# TYPE librenms_alert_rules_by_severity gauge';
        $sevs = AlertRule::select('severity', DB::raw('count(*) as cnt'))->groupBy('severity')->get();
        foreach ($sevs as $sv) {
            $sev = $this->escapeLabel((string) ($sv->severity ?? 'unknown'));
            $lines[] = sprintf('librenms_alert_rules_by_severity{severity="%s"} %d', $sev, $sv->cnt);
        }

        // Active/acknowledged counts
        $activeQ = Alert::where('state', 1);
        $ackQ = Alert::where('state', 2);
        $active = $this->applyDeviceFilter($activeQ, $filters['device_ids'])->count();
        $ack = $this->applyDeviceFilter($ackQ, $filters['device_ids'])->count();
        $lines[] = '# HELP librenms_alerts_active Number of active alerts';
        $lines[] = '# TYPE librenms_alerts_active gauge';
        $lines[] = "librenms_alerts_active {$active}";
        $lines[] = '# HELP librenms_alerts_acknowledged Number of acknowledged alerts';
        $lines[] = '# TYPE librenms_alerts_acknowledged gauge';
        $lines[] = "librenms_alerts_acknowledged {$ack}";

        return implode("\n", $lines) . "\n";
    }
}
