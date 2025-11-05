<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Alert;
use App\Models\AlertRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlertsMetrics
{
    use Traits\MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Parse filters
        $filters = $this->parseDeviceFilters($request);

        // Gather global metrics
        $total_rules = AlertRule::count();
        $this->appendMetricBlock($lines, 'librenms_alerts_rules_total', 'Total number of alert rules', 'gauge', "librenms_alerts_rules_total {$total_rules}");

        $alertsQ = Alert::query();
        $alertsQ = $this->applyDeviceFilter($alertsQ, $filters['device_ids']);
        $total_alerts = $alertsQ->count();
        $this->appendMetricBlock($lines, 'librenms_alerts_total', 'Total number of alerts rows', 'gauge', "librenms_alerts_total {$total_alerts}");

        // Alerts by state
        $state_lines = [];
        $statesQ = Alert::select('state', DB::raw('count(*) as cnt'))->groupBy('state');
        $statesQ = $this->applyDeviceFilter($statesQ, $filters['device_ids']);
        $states = $statesQ->get();
        /** @var \stdClass $s */
        foreach ($states as $s) {
            $state_lines[] = sprintf('librenms_alerts_by_state{state="%s"} %d', $s->state, $s->cnt);
        }
        $this->appendMetricBlock($lines, 'librenms_alerts_by_state', 'Number of alerts by state', 'gauge', $state_lines);

        // Rules by severity
        $severity_lines = [];
        $sevs = AlertRule::select('severity', DB::raw('count(*) as cnt'))->groupBy('severity')->get();
        /** @var \stdClass $sv */
        foreach ($sevs as $sv) {
            $sev = $this->escapeLabel((string) ($sv->severity ?? 'unknown'));
            $severity_lines[] = sprintf('librenms_alerts_rules_by_severity{severity="%s"} %d', $sev, $sv->cnt);
        }
        $this->appendMetricBlock($lines, 'librenms_alerts_rules_by_severity', 'Number of alert rules by severity', 'gauge', $severity_lines);

        // Active alert counts
        $activeQ = Alert::where('state', 1);
        $active = $this->applyDeviceFilter($activeQ, $filters['device_ids'])->count();
        $this->appendMetricBlock($lines, 'librenms_alerts_active', 'Number of active alerts', 'gauge', "librenms_alerts_active {$active}");

        // Acknowledged alert counts
        $ackQ = Alert::where('state', 2);
        $ack = $this->applyDeviceFilter($ackQ, $filters['device_ids'])->count();
        $this->appendMetricBlock($lines, 'librenms_alerts_acknowledged', 'Number of acknowledged alerts', 'gauge', "librenms_alerts_acknowledged {$ack}");

        return implode("\n", $lines) . "\n";
    }
}
