<?php

namespace App\Http\Formatters;

use App\Models\Port;
use App\Models\Sensor;
use App\Models\StateTranslation;
use Illuminate\Support\Str;
use LibreNMS\Util\Number;
use LibreNMS\Util\Url;

class AlertLogDetailFormatter
{
    public function format(array $alert_details): string
    {
        $all_fault_detail = '';

        // Check if we have a diff (alert status changed, worse and better)
        if (isset($alert_details['diff'])) {
            // Add a "title" for the modifications
            $all_fault_detail .= '<b>Modifications:</b><br>';

            // Check if we have added
            foreach (array_values($alert_details['diff']['added'] ?? []) as $index => $tmp_alerts_added) {
                $all_fault_detail .= $this->formatDetails($index, $tmp_alerts_added, 'Added');
            }

            // Check if we have resolved
            foreach (array_values($alert_details['diff']['resolved'] ?? []) as $index => $tmp_alerts_resolved) {
                $all_fault_detail .= $this->formatDetails($index, $tmp_alerts_resolved, 'Resolved');
            }

            // Add a "title" for the complete list
            $all_fault_detail .= '<br><b>All current items:</b><br>';
        }

        foreach ($alert_details['rule'] ?? [] as $index => $tmp_alerts_rule) {
            $all_fault_detail .= $this->formatDetails($index, $tmp_alerts_rule);
        }

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
        $fault_detail .= empty($items)
            ? $this->fallbackFormatting($alert_detail)
            : implode('<br>', $items);

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

            $lines[] = $this->line($k, $v);
        }

        return $this->lines($lines);
    }

    private function formatBill(array $detail): ?string
    {
        if (empty($detail['bill_id'])) {
            return null;
        }

        $bill_url = Url::generate(['page' => 'bill', 'bill_id' => $detail['bill_id']]);

        return $this->linkLine('Bill', $bill_url, $detail['bill_name'] ?? 'Bill');
    }

    private function formatPort(array $detail): ?string
    {
        if (empty($detail['port_id'])) {
            return null;
        }

        $port = new Port($detail);

        return $this->lines([
            $this->line('Port', Url::portLink($port), escape: false),
            ($port->ifAlias && $port->ifAlias != $port->ifDescr)
                ? $this->line('Alias', $port->ifAlias)
                : null,
            !empty($detail['isisISAdjState'])
                ? $this->line('Adjacent', $detail['isisISAdjIPAddrAddress'] ?? 'Unknown')
                : null,
        ]);
    }

    private function formatAccessPoint(array $detail): ?string
    {
        if (empty($detail['accesspoint_id'])) {
            return null;
        }

        $ap_url = Url::deviceUrl(
            $detail['device_id'] ?? 0,
            ['tab' => 'accesspoints', 'ap' => $detail['accesspoint_id']]
        );

        return $this->linkLine('Access Point', $ap_url, $detail['name'] ?? 'Access Point');
    }

    private function formatSensor(array $detail): ?string
    {
        if (empty($detail['sensor_id'])) {
            return null;
        }

        $sensor = new Sensor($detail);
        $sensor->sensor_id = $detail['sensor_id'];

        // Pre-load translation for formatValue if it exists
        if (isset($detail['state_descr'])) {
            $translation = new StateTranslation($detail);
            $sensor->setRelation('translations', collect([$translation]));
        }

        $value = $sensor->formatValue();
        $value_line = $sensor->sensor_class == 'state'
            ? $this->line('State', $value . ' (numerical: ' . $sensor->sensor_current . ')')
            : $this->line('Value', $value . ' (' . $sensor->sensor_class . ')');

        // Build thresholds
        $thresholds = $this->inlineList([
            $sensor->sensor_limit_low ? 'Low: ' . e($sensor->sensor_limit_low) : null,
            $sensor->sensor_limit_low_warn ? 'Low Warn: ' . e($sensor->sensor_limit_low_warn) : null,
            $sensor->sensor_limit_warn ? 'High Warn: ' . e($sensor->sensor_limit_warn) : null,
            $sensor->sensor_limit ? 'High: ' . e($sensor->sensor_limit) : null,
        ]);

        return $this->lines([
            $this->line('Sensor', Url::sensorLink($sensor), escape: false),
            $value_line,
            $thresholds ? $this->line('Thresholds', $thresholds, escape: false) : null,
        ]);
    }

    private function formatService(array $detail): ?string
    {
        if (empty($detail['service_id'])) {
            return null;
        }

        $device_id = $detail['device_id'] ?? 0;
        $service_name = $detail['service_name'] ?? 'Service';

        if (isset($detail['service_type'])) {
            $service_name .= ' (' . e($detail['service_type']) . ')';
        }

        $service_url = Url::deviceUrl($device_id, ['tab' => 'services', 'view' => 'detail']);
        $service_host = ! empty($detail['service_ip'])
            ? $detail['service_ip']
            : ($detail['hostname'] ?? 'Unknown');

        return $this->lines([
            $this->linkLine('Service', $service_url, $service_name),
            $this->line('Host', $service_host),
            $this->line('Description', $detail['service_desc'] ?? null),
            $this->line('Param', $detail['service_param'] ?? null),
            $this->line('Message', $detail['service_message'] ?? null),
        ]);
    }

    private function formatBgpPeer(array $detail): ?string
    {
        if (empty($detail['bgpPeer_id'])) {
            return null;
        }

        $bgp_url = Url::deviceUrl($detail['device_id'] ?? 0, ['tab' => 'routing', 'proto' => 'bgp']);

        return $this->lines([
            $this->linkLine('BGP Peer', $bgp_url, $detail['bgpPeerIdentifier'] ?? 'BGP Peer'),
            $this->line('Description', $detail['bgpPeerDescr'] ?? null),
            $this->line('Remote AS', $detail['bgpPeerRemoteAs'] ?? null),
            $this->line('State', $detail['bgpPeerState'] ?? null),
        ]);
    }

    private function formatMempool(array $detail): ?string
    {
        if (empty($detail['mempool_id'])) {
            return null;
        }

        $mempool_url = Url::graphPageUrl('mempool_usage', ['id' => $detail['mempool_id']]);

        // Build usage statistics inline
        $usage = $this->inlineList([
            isset($detail['mempool_perc']) ? 'Usage: ' . e(Number::normalizePercent($detail['mempool_perc'])) : null,
            isset($detail['mempool_free']) ? 'Free: ' . e(Number::formatSi($detail['mempool_free'])) : null,
            isset($detail['mempool_total']) ? 'Total: ' . e(Number::formatSi($detail['mempool_total'])) : null,
        ]);

        return $this->lines([
            $this->linkLine('Memory Pool', $mempool_url, $detail['mempool_descr'] ?? 'Memory Pool'),
            $usage,
        ]);
    }

    private function formatApplication(array $detail): ?string
    {
        if (empty($detail['app_id'])) {
            return null;
        }

        $app_type = $detail['app_type'] ?? 'Application';
        $app_url = Url::deviceUrl($detail['device_id'] ?? 0, ['tab' => 'apps', 'app' => $app_type]);

        return $this->lines([
            $this->linkLine('Application', $app_url, $app_type),
            $this->line('Status', $detail['app_status'] ?? null),
            !empty($detail['metric'])
                ? $this->line('Metric', e($detail['metric']) . ' = ' . e($detail['value'] ?? 'N/A'), escape: false)
                : null,
        ]);
    }

    // ========================================
    // Helper Methods for Consistent Formatting
    // ========================================

    /**
     * Format a labeled line with optional value
     */
    private function line(string $label, mixed $value, bool $escape = true): ?string
    {
        if (empty($value) && $value !== '0' && $value !== 0) {
            return null;
        }

        $formatted_value = $escape ? e($value) : $value;
        return $label . ': ' . $formatted_value;
    }

    /**
     * Format a labeled link line
     */
    private function linkLine(string $label, string $url, string $text): string
    {
        return $label . ': <a href="' . e($url) . '">' . e($text) . '</a>';
    }

    /**
     * Join non-null values with comma separator
     */
    private function inlineList(array $items): ?string
    {
        $filtered = array_filter($items, fn($item) => $item !== null && $item !== '');
        return empty($filtered) ? null : implode(', ', $filtered);
    }

    /**
     * Join non-null lines with <br> separator
     */
    private function lines(array $lines): string
    {
        return implode('<br>', array_filter($lines, fn($line) => $line !== null && $line !== ''));
    }
}
