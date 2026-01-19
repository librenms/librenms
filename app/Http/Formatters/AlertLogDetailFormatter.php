<?php

namespace App\Http\Formatters;

use App\Facades\DeviceCache;
use App\Facades\PortCache;
use App\Models\Sensor;
use App\Models\StateTranslation;
use Illuminate\Support\Str;
use LibreNMS\Util\Number;
use LibreNMS\Util\Url;

class AlertLogDetailFormatter
{
    public function format(array $alert_details): string
    {
        $details = $alert_details;
        $max_row_length = 0;
        $all_fault_detail = '';

        // Check if we have a diff (alert status changed, worse and better)
        if (isset($details['diff'])) {
            // Add a "title" for the modifications
            $all_fault_detail .= '<b>Modifications:</b><br>';

            // Check if we have added
            if (isset($details['diff']['added'])) {
                foreach (array_values($details['diff']['added'] ?? []) as $oa => $tmp_alerts_added) {
                    $fault_detail = $this->formatDetails($oa, $tmp_alerts_added, 'Added');
                    $max_row_length = strlen(strip_tags((string) $fault_detail)) > $max_row_length ? strlen(strip_tags((string) $fault_detail)) : $max_row_length;
                    $all_fault_detail .= $fault_detail;
                }//end foreach
            }

            // Check if we have resolved
            if (isset($details['diff']['resolved'])) {
                foreach (array_values($details['diff']['resolved'] ?? []) as $or => $tmp_alerts_resolved) {
                    $fault_detail = $this->formatDetails($or, $tmp_alerts_resolved, 'Resolved');
                    $max_row_length = strlen(strip_tags((string) $fault_detail)) > $max_row_length ? strlen(strip_tags((string) $fault_detail)) : $max_row_length;
                    $all_fault_detail .= $fault_detail;
                }//end foreach
            }

            // Add a "title" for the complete list
            $all_fault_detail .= '<br><b>All current items:</b><br>';
        }

        foreach ($details['rule'] ?? [] as $o => $tmp_alerts_rule) {
            $fault_detail = $this->formatDetails($o, $tmp_alerts_rule);
            $max_row_length = strlen(strip_tags((string) $fault_detail)) > $max_row_length ? strlen(strip_tags((string) $fault_detail)) : $max_row_length;
            $all_fault_detail .= $fault_detail;
        }//end foreach

        return $all_fault_detail;
    }

    private function formatDetails(int $row, array $alert_detail, ?string $type_info = null): string
    {
        $items = array_filter([
            $this->formatBill($alert_detail),
            $this->formatPort($alert_detail),
            $this->formatSensor($alert_detail),
            $this->formatAccessPoint($alert_detail),
            $this->formatService($alert_detail),
            $this->formatBgpPeer($alert_detail),
            $this->formatMempool($alert_detail),
            $this->formatApplication($alert_detail),
        ]);

        $fault_detail = $type_info ? $type_info . ' ' : '';
        $fault_detail .= '#' . ($row + 1) . ': ';
        $fault_detail .= implode('; ', $items);

        if (empty($items)) {
            $fault_detail .= $this->fallbackFormatting($alert_detail);
        }

        return $fault_detail . '<br>';
    }

    private function fallbackFormatting(array $detail): string
    {
        $skip_keys = ['device_id', 'sysObjectID', 'sysDescr', 'location_id'];
        $skip_key_contains = ['id', 'desc', 'msg', 'last'];

        $lines = [];
        foreach ($detail as $k => $v) {
            if (empty($v)) {
                continue;
            }

            if (in_array($k, $skip_keys)) {
                continue;
            }

            if (Str::contains($k, $skip_key_contains, ignoreCase: true)) {
                continue;
            }

            $lines[] = e($k) . ' => ' . e($v);
        }

        return implode('<br>', $lines);
    }

    private function formatBill(array $detail): ?string
    {
        if (empty($detail['bill_id'])) {
            return null;
        }

        $bill_id = $detail['bill_id'];
        $bill_name = $detail['bill_name'] ?? '';
        $bill_url = Url::generate(['page' => 'bill', 'bill_id' => $bill_id]);

        return $this->simpleLink($bill_url, $bill_name);
    }

    private function formatPort(array $detail): ?string
    {
        if (empty($detail['port_id'])) {
            return null;
        }

        $output = '';

        if (! empty($detail['isisISAdjState'])) {
            $output .= 'Adjacent: ' . e($detail['isisISAdjIPAddrAddress'] ?? '') . ', Interface: ';
        }

        $port = PortCache::get($detail['port_id']);
        $output .= Url::portLink($port);
        if ($port->ifAlias && $port->ifAlias != $port->ifDescr) {
            $output .= '; ' . e($port->ifAlias);
        }

        return $output;
    }

    private function formatAccessPoint(array $detail): ?string
    {
        if (empty($detail['accesspoint_id'])) {
            return null;
        }

        $device_id = $detail['device_id'] ?? 0;
        $ap_id = $detail['accesspoint_id'];
        $ap_name = $detail['name'] ?? ''; // could be wrong
        $ap_url = Url::deviceUrl($device_id, ['tab' => 'accesspoints', 'ap' => $ap_id]);

        return $this->simpleLink($ap_url, $ap_name);
    }

    private function formatSensor(array $detail): ?string
    {
        if (empty($detail['sensor_id'])) {
            return null;
        }

        $sensor = new Sensor($detail); // should work
        $sensor->sensor_id = $detail['sensor_id'];

        // pre-load translation for formatValue if it exists
        if (isset($detail['state_descr'])) {
            $translation = new StateTranslation($detail);
            $sensor->setRelation('translations', collect([$translation]));
        }

        $value = $sensor->formatValue();
        $description = $sensor->sensor_class == 'state'
            ? "State: $value (numerical $sensor->sensor_current)"
            : "Value: $value ($sensor->sensor_class)";

        $thresholds = [];
        if ($sensor->sensor_limit_low) {
            $thresholds[] = 'low: ' . $sensor->sensor_limit_low;
        }
        if ($sensor->sensor_limit_low_warn) {
            $thresholds[] = 'low_warn: ' . $sensor->sensor_limit_low_warn;
        }
        if ($sensor->sensor_limit_warn) {
            $thresholds[] = 'high_warn: ' . $sensor->sensor_limit_warn;
        }
        if ($sensor->sensor_limit) {
            $thresholds[] = 'high: ' . $sensor->sensor_limit;
        }

        return Url::sensorLink($sensor) . '<br>' . e($description) . '<br>' . e(implode(', ', $thresholds));
    }

    private function formatService(array $detail): ?string
    {
        if (empty($detail['service_id'])) {
            return null;
        }

        $device_id = $detail['device_id'] ?? 0;
        $service_name = $detail['service_name'] ?? '';
        if (isset($detail['service_type'])) {
            $service_name .= " ({$detail['service_type']})";
        }
        $service_url = Url::deviceUrl($device_id, ['tab' => 'services', 'view' => 'detail']);
        $service_link = $this->simpleLink($service_url, $service_name);
        $service_host = empty($detail['service_ip']) ? DeviceCache::get($device_id)->displayName() : $detail['service_ip'];

        $description = 'Service: ' . $service_link . '<br>Service Host: ' . e($service_host) . '<br>';

        if (! empty($detail['service_desc'])) {
            $description .= 'Description: ' . e($detail['service_desc']) . '<br>';
        }

        if (! empty($detail['service_param'])) {
            $description .= 'Param: ' . e($detail['service_param']) . '<br>';
        }

        return $description . 'Msg: ' . e($detail['service_message'] ?? '');
    }

    private function formatBgpPeer(array $detail): ?string
    {
        if (empty($detail['bgpPeer_id'])) {
            return null;
        }

        $bgp_url = Url::deviceUrl($detail['device_id'] ?? 0, ['tab' => 'routing', 'proto' => 'bgp']);
        $bgp_peer_id = $detail['bgpPeerIdentifier'] ?? '';

        $description = $this->simpleLink($bgp_url, $bgp_peer_id);

        if (! empty($detail['bgpPeerDescr'])) {
            $description .= ', Desc ' . e($detail['bgpPeerDescr'] ?? '');
        }

        if (! empty($detail['bgpPeerRemoteAs'])) {
            $description .= ', AS' . e($detail['bgpPeerRemoteAs']);
        }

        if (! empty($detail['bgpPeerState'])) {
            $description .= ', State ' . e($detail['bgpPeerState']);
        }

        return $description;
    }

    private function formatMempool(array $detail): ?string
    {
        if (empty($detail['mempool_id'])) {
            return null;
        }

        $mempool_url = Url::graphPageUrl('mempool_usage', ['id' => $detail['mempool_id']]);
        $mempool_perc = Number::normalizePercent($detail['mempool_perc'] ?? '');
        $mempool_free = Number::formatSi($detail['mempool_free'] ?? '');
        $mempool_total = Number::formatSi($detail['mempool_total'] ?? '');

        $description = 'Memory Pool: ' . $this->simpleLink($mempool_url, $detail['mempool_descr'] ?? 'link');
        $description .= '<br> Usage ' . e($mempool_perc) . '%, Free' . e($mempool_free) . ', Size ' . e($mempool_total);

        return $description;
    }

    private function formatApplication(array $detail): ?string
    {
        if (empty($detail['app_id'])) {
            return null;
        }

        $app_type = $detail['app_type'] ?? 'app';
        $app_url = Url::deviceUrl($detail['device_id'] ?? 0, ['tab' => 'apps', 'app' => $app_type]);

        $description = $this->simpleLink($app_url, $app_type);
        if (! empty($detail['app_status'])) {
            $description .= ' => ' . e($detail['app_status']);
        }

        // FIXME is this correct?
        if (! empty($detail['metric'])) {
            $description .= ' : ' . e($detail['metric']) . ' => ' . e($detail['value']);
        }

        return $description;
    }

    private function simpleLink(string $url, string $text): string
    {
        return '<a href="' . e($url) . '">' . e($text) . '</a>';
    }
}
