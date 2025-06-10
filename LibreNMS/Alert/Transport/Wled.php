<?php

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Wled extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $host = $this->config['wled-host'];
        $port = $this->config['wled-port'];

        if ($alert_data['state'] === AlertState::RECOVERED) {
            $wled_preset = $this->config['wled-recovery'];
        } elseif ($alert_data['severity'] === 'critical') {
            $wled_preset = $this->config['wled-critical'];
        } elseif ($alert_data['severity'] === 'warning') {
            $wled_preset = $this->config['wled-warning'];
        }

        // if this is not set or blank, it means it is a unsupported state or to be ignored
        if (! isset($wled_preset) || $wled_preset === '') {
            return true;
        }

        $data = ['ps' => $wled_preset];
        $url = 'http://' . $host . '/json';

        $res = Http::client()
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), '', $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Hostname',
                    'name' => 'wled-host',
                    'descr' => 'WLED Hostname',
                    'type' => 'text',
                ],
                [
                    'title' => 'Critical',
                    'name' => 'wled-critical',
                    'descr' => 'ID of the preset to use for criticals. Leave blank to ignore.',
                    'type' => 'text',
                ],
                [
                    'title' => 'Warning',
                    'name' => 'wled-warning',
                    'descr' => 'ID of the preset to use for warnings. Leave blank to ignore.',
                    'type' => 'text',
                ],
                [
                    'title' => 'Recovery',
                    'name' => 'wled-recovery',
                    'descr' => 'ID of the preset to use for recoveries. Leave blank to ignore.',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'wled-host' => 'required|string',
                'wled-critical' => 'integer|between:1,65536',
                'wled-warning' => 'integer|between:1,65536',
                'wled-recovery' => 'integer|between:1,65536',
            ],
        ];
    }
}
