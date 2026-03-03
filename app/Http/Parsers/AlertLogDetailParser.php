<?php

namespace App\Http\Parsers;

use App\Models\Port;
use App\Models\Sensor;
use App\Models\StateTranslation;
use Illuminate\Support\Str;
use LibreNMS\Util\Number;
use LibreNMS\Util\Url;

/**
 * Alert log detail parser that converts alert details into structured arrays
 */
class AlertLogDetailParser
{
    /** @var array<int, array{label: string, value: string, url?: string}> */
    private array $fields = [];

    /**
     * Parse alert details into structured array
     *
     * @param  array  $alert_details
     * @return array{sections: array<int, array{title?: string, items: array<int, array{row: int, type?: string, fields: array<int, array{label: string, value: string, url?: string}>}>}>}
     */
    public function parse(array $alert_details): array
    {
        $sections = [];

        if (isset($alert_details['diff'])) {
            $items = [];

            foreach (array_values($alert_details['diff']['added'] ?? []) as $index => $tmp_alerts_added) {
                $items[] = $this->parseItem($index, $tmp_alerts_added, 'added');
            }

            foreach (array_values($alert_details['diff']['resolved'] ?? []) as $index => $tmp_alerts_resolved) {
                $items[] = $this->parseItem($index, $tmp_alerts_resolved, 'resolved');
            }

            $sections[] = [
                'title' => 'Modifications',
                'items' => $items,
            ];
        }

        // Process main rule items
        $items = [];
        foreach ($alert_details['rule'] ?? [] as $index => $tmp_alerts_rule) {
            $items[] = $this->parseItem($index, $tmp_alerts_rule);
        }

        $section = ['items' => $items];
        if (isset($alert_details['diff'])) {
            $section = ['title' => 'All current items'] + $section;
        }
        $sections[] = $section;

        return ['sections' => $sections];
    }

    /**
     * Parse a single item using available parsers or fallback
     *
     * @param  int  $row
     * @param  array  $alert_detail
     * @param  string|null  $type
     * @return array{row: int, type?: string, fields: array<int, array{label: string, value: string, url?: string}>}
     */
    private function parseItem(int $row, array $alert_detail, ?string $type = null): array
    {
        $this->fields = [];

        // Try all parsers - multiple can succeed
        $this->parseBill($alert_detail);
        $this->parsePort($alert_detail);
        $this->parseSensor($alert_detail);
        $this->parseAccessPoint($alert_detail);
        $this->parseService($alert_detail);
        $this->parseBgpPeer($alert_detail);
        $this->parseMempool($alert_detail);
        $this->parseApplication($alert_detail);

        // If no parser added fields, use fallback
        if (empty($this->fields)) {
            $this->parseFallback($alert_detail);
        }

        $item = [
            'row' => $row,
            'fields' => $this->fields,
        ];

        if ($type !== null) {
            $item['type'] = $type;
        }

        return $item;
    }

    /**
     * Add a field to the current item being built
     */
    private function addField(string $label, string $value, ?string $url = null): void
    {
        $field = [
            'label' => $label,
            'value' => $value,
        ];

        if ($url !== null) {
            $field['url'] = $url;
        }

        $this->fields[] = $field;
    }

    // ========================================
    // Entity Parsers
    // ========================================

    private function parseBill(array $detail): void
    {
        if (empty($detail['bill_id'])) {
            return;
        }

        $this->addField(
            'Bill',
            $detail['bill_name'] ?? 'Bill',
            Url::generate(['page' => 'bill', 'bill_id' => $detail['bill_id']])
        );
    }

    private function parsePort(array $detail): void
    {
        if (empty($detail['port_id'])) {
            return;
        }

        $port = new Port($detail);

        $this->addField(
            'Port',
            $port->getLabel(),
            Url::portUrl($port)
        );

        if ($port->ifAlias && $port->ifAlias != $port->ifDescr) {
            $this->addField('Alias', $port->ifAlias);
        }

        if (! empty($detail['isisISAdjState'])) {
            $this->addField('Adjacent', $detail['isisISAdjIPAddrAddress'] ?? 'Unknown');
        }
    }

    private function parseSensor(array $detail): void
    {
        if (empty($detail['sensor_id'])) {
            return;
        }

        $sensor = new Sensor($detail);
        $sensor->sensor_id = $detail['sensor_id'];

        // Pre-load translation for formatValue if it exists
        if (isset($detail['state_descr'])) {
            $translation = new StateTranslation($detail);
            $sensor->setRelation('translations', collect([$translation]));
        }

        $value = $sensor->formatValue();

        $this->addField(
            'Sensor',
            $sensor->sensor_descr,
            Url::sensorUrl($sensor)
        );

        if ($sensor->sensor_class == 'state') {
            $this->addField('State', $value . ' (numerical: ' . $sensor->sensor_current . ')');
        } else {
            $this->addField('Value', $value . ' (' . $sensor->sensor_class . ')');
        }

        // Add thresholds if any exist
        $thresholds = [];
        if ($sensor->sensor_limit_low) {
            $thresholds[] = 'Low: ' . $sensor->sensor_limit_low;
        }
        if ($sensor->sensor_limit_low_warn) {
            $thresholds[] = 'Low Warn: ' . $sensor->sensor_limit_low_warn;
        }
        if ($sensor->sensor_limit_warn) {
            $thresholds[] = 'High Warn: ' . $sensor->sensor_limit_warn;
        }
        if ($sensor->sensor_limit) {
            $thresholds[] = 'High: ' . $sensor->sensor_limit;
        }

        if (! empty($thresholds)) {
            $this->addField('Thresholds', implode(', ', $thresholds));
        }
    }

    private function parseAccessPoint(array $detail): void
    {
        if (empty($detail['accesspoint_id'])) {
            return;
        }

        $this->addField(
            'Access Point',
            $detail['name'] ?? 'Access Point',
            Url::deviceUrl($detail['device_id'] ?? 0, ['tab' => 'accesspoints', 'ap' => $detail['accesspoint_id']])
        );
    }

    private function parseService(array $detail): void
    {
        if (empty($detail['service_id'])) {
            return;
        }

        $service_name = $detail['service_name'] ?? 'Service';
        if (isset($detail['service_type'])) {
            $service_name .= ' (' . $detail['service_type'] . ')';
        }

        $this->addField(
            'Service',
            $service_name,
            Url::deviceUrl($detail['device_id'] ?? 0, ['tab' => 'services', 'view' => 'detail'])
        );

        $service_host = ! empty($detail['service_ip'])
            ? $detail['service_ip']
            : ($detail['hostname'] ?? 'Unknown');

        $this->addField('Host', $service_host);

        if (! empty($detail['service_desc'])) {
            $this->addField('Description', $detail['service_desc']);
        }

        if (! empty($detail['service_param'])) {
            $this->addField('Param', $detail['service_param']);
        }

        if (! empty($detail['service_message'])) {
            $this->addField('Message', $detail['service_message']);
        }
    }

    private function parseBgpPeer(array $detail): void
    {
        if (empty($detail['bgpPeer_id'])) {
            return;
        }

        $this->addField(
            'BGP Peer',
            $detail['bgpPeerIdentifier'] ?? 'BGP Peer',
            Url::deviceUrl($detail['device_id'] ?? 0, ['tab' => 'routing', 'proto' => 'bgp'])
        );

        if (! empty($detail['bgpPeerDescr'])) {
            $this->addField('Description', $detail['bgpPeerDescr']);
        }

        if (! empty($detail['bgpPeerRemoteAs'])) {
            $this->addField('Remote AS', (string) $detail['bgpPeerRemoteAs']);
        }

        if (! empty($detail['bgpPeerState'])) {
            $this->addField('State', $detail['bgpPeerState']);
        }
    }

    private function parseMempool(array $detail): void
    {
        if (empty($detail['mempool_id'])) {
            return;
        }

        $this->addField(
            'Memory Pool',
            $detail['mempool_descr'] ?? 'Memory Pool',
            Url::graphPageUrl('mempool_usage', ['id' => $detail['mempool_id']])
        );

        // Build usage statistics
        $usage = [];
        if (isset($detail['mempool_perc'])) {
            $usage[] = 'Usage: ' . Number::normalizePercent($detail['mempool_perc']);
        }
        if (isset($detail['mempool_free'])) {
            $usage[] = 'Free: ' . Number::formatSi($detail['mempool_free']);
        }
        if (isset($detail['mempool_total'])) {
            $usage[] = 'Total: ' . Number::formatSi($detail['mempool_total']);
        }

        if (! empty($usage)) {
            $this->addField('Usage', implode(', ', $usage));
        }
    }

    private function parseApplication(array $detail): void
    {
        if (empty($detail['app_id'])) {
            return;
        }

        $app_type = $detail['app_type'] ?? 'Application';

        $this->addField(
            'Application',
            $app_type,
            Url::deviceUrl($detail['device_id'] ?? 0, ['tab' => 'apps', 'app' => $app_type])
        );

        if (! empty($detail['app_status'])) {
            $this->addField('Status', $detail['app_status']);
        }

        if (! empty($detail['metric'])) {
            $this->addField('Metric', $detail['metric'] . ' = ' . ($detail['value'] ?? 'N/A'));
        }
    }

    private function parseFallback(array $detail): void
    {
        $skip_keys = [
            'device_id',
            'sysObjectID',
            'sysDescr',
            'location_id',
            'overwrite_ip',
            'port',
            'transport',
            'icon',
            'max_depth',
            'port_association_mode',
            'agent_uptime',
            'poller_group',
            'inserted',
        ];
        $skip_key_contains = ['id', 'desc', 'msg', 'last', 'auth', 'pass', 'snmp', 'community'];

        foreach ($detail as $k => $v) {
            if (empty($v) && $v !== '0' && $v !== 0) {
                continue;
            }

            if (in_array($k, $skip_keys)) {
                continue;
            }

            if (Str::contains($k, $skip_key_contains, ignoreCase: true)) {
                continue;
            }

            $this->addField($k, (string) $v);
        }
    }
}
