<?php
/**
 * AlertOps API Transport
 *
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Alertops extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['alertops-url'];

        $data = [
            'device_id' => $alert_data['device_id'],
            'hostname' => $alert_data['hostname'],
            'sysName' => $alert_data['sysName'],
            'sysDescr' => $alert_data['sysDescr'],
            'sysContact' => $alert_data['sysContact'],
            'os' => $alert_data['os'],
            'type' => $alert_data['type'],
            'ip' => $alert_data['ip'],
            'hardware' => $alert_data['hardware'],
            'version' => $alert_data['version'],
            'features' => $alert_data['features'],
            'serial' => $alert_data['serial'],
            'location' => $alert_data['location'],
            'uptime' => $alert_data['uptime'],
            'uptime_short' => $alert_data['uptime_short'],
            'uptime_long' => $alert_data['uptime_long'],
            'description' => $alert_data['description'],
            'notes' => $alert_data['notes'],
            'alert_notes' => $alert_data['alert_notes'],
            'title' => $alert_data['title'],
            'elapsed' => $alert_data['elapsed'],
            'builder' => $alert_data['builder'],
            'id' => $alert_data['id'],
            'uid' => $alert_data['uid'],
            'state' => $alert_data['state'],
            'severity' => $alert_data['severity'],
            'rule' => $alert_data['rule'],
            'name' => $alert_data['name'],
            'proc' => $alert_data['proc'],
            'timestamp' => $alert_data['timestamp'],
        ];

        $res = Http::client()->post($url, ['json' => $data]);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), '', $alert_data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'alertops-url',
                    'descr' => 'AlertOps Webhook URL',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'alertops-url' => 'required|url',
            ],
        ];
    }
}
