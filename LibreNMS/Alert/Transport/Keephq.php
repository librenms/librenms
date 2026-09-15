<?php

/**
 * Keephq.php
 *
 * LibreNMS KeepHQ alert transport
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Keephq extends Transport
{
    /** @param  array<string, mixed>  $alert_data */
    public function deliverAlert(array $alert_data): bool
    {
        $payload = [
            'title' => $alert_data['name'] . ': ' . ($alert_data['hostname'] ?? ''),
            'hostname' => $alert_data['hostname'] ?? '',
            'device_id' => $alert_data['device_id'] ?? 0,
            'sysDescr' => $alert_data['sysDescr'] ?? '',
            'sysName' => $alert_data['sysName'] ?? '',
            'sysContact' => $alert_data['sysContact'] ?? '',
            'os' => $alert_data['os'] ?? '',
            'type' => $alert_data['type'] ?? '',
            'ip' => $alert_data['ip'] ?? '',
            'display' => $alert_data['display'] ?? '',
            'version' => $alert_data['version'] ?? '',
            'hardware' => $alert_data['hardware'] ?? '',
            'features' => $alert_data['features'] ?? '',
            'serial' => $alert_data['serial'] ?? '',
            'status' => $alert_data['status'] ?? '',
            'status_reason' => $alert_data['status_reason'] ?? '',
            'location' => $alert_data['location'] ?? '',
            'description' => $alert_data['description'] ?? '',
            'notes' => $alert_data['notes'] ?? '',
            'uptime' => $alert_data['uptime'] ?? '',
            'uptime_short' => $alert_data['uptime_short'] ?? '',
            'uptime_long' => $alert_data['uptime_long'] ?? '',
            'elapsed' => $alert_data['elapsed'] ?? '',
            'alerted' => $alert_data['alerted'] ?? '',
            'alert_id' => $alert_data['id'] ?? 0,
            'alert_notes' => $alert_data['alert_notes'] ?? '',
            'proc' => $alert_data['proc'] ?? '',
            'rule_id' => $alert_data['rule_id'] ?? 0,
            'id' => $alert_data['id'] ?? 0,
            'faults' => $alert_data['faults'] ?? [],
            'uid' => $alert_data['uid'] ?? '',
            'severity' => $alert_data['severity'] ?? '',
            'rule' => $alert_data['rule'] ?? '',
            'name' => $alert_data['name'] ?? '',
            'string' => $alert_data['string'] ?? '',
            'timestamp' => $alert_data['timestamp'] ?? '',
            'contacts' => $alert_data['contacts'] ?? [],
            'state' => $alert_data['state'] ?? 0,
            'msg' => $alert_data['msg'] ?? '',
            'builder' => $alert_data['builder'] ?? '',
        ];

        $res = Http::client()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-API-KEY' => $this->config['keephq-api-key'],
            ])
            ->acceptJson()
            ->post($this->config['keephq-api-url'], $payload);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $alert_data['msg'], $payload);
    }

    /** @return array<string, array<int|string, mixed>> */
    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'API URL',
                    'name' => 'keephq-api-url',
                    'descr' => 'KeepHQ API endpoint URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'API Key',
                    'name' => 'keephq-api-key',
                    'descr' => 'KeepHQ API key (webhook role)',
                    'type' => 'password',
                ],
            ],
            'validation' => [
                'keephq-api-url' => 'required|url',
                'keephq-api-key' => 'required|string',
            ],
        ];
    }
}
