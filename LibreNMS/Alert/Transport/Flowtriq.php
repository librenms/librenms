<?php

/* Copyright (C) 2026 Flowtriq
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>. */

/**
 * Flowtriq Alert Transport
 *
 * @author Flowtriq <support@flowtriq.com>
 * @copyright 2026 Flowtriq
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Flowtriq extends Transport
{
    /** @param  array<string, mixed>  $alert_data */
    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['flowtriq-url'];

        $alert_status = match ($alert_data['state']) {
            AlertState::RECOVERED => 'recovered',
            AlertState::ACKNOWLEDGED => 'acknowledged',
            AlertState::WORSE => 'worse',
            AlertState::BETTER => 'better',
            default => 'alert',
        };

        $data = [
            'source' => 'librenms',
            'status' => $alert_status,
            'alert_id' => $alert_data['alert_id'] ?? null,
            'severity' => $alert_data['severity'] ?? null,
            'title' => $alert_data['title'] ?? null,
            'message' => strip_tags($alert_data['msg'] ?? ''),
            'rule' => $alert_data['rule'] ?? null,
            'timestamp' => $alert_data['timestamp'] ?? null,
            'device' => [
                'device_id' => $alert_data['device_id'] ?? null,
                'hostname' => $alert_data['hostname'] ?? null,
                'sysName' => $alert_data['sysName'] ?? null,
                'os' => $alert_data['os'] ?? null,
                'type' => $alert_data['type'] ?? null,
                'ip' => $alert_data['ip'] ?? null,
                'hardware' => $alert_data['hardware'] ?? null,
                'location' => $alert_data['location'] ?? null,
            ],
        ];

        $client = Http::client()->acceptJson();

        $api_key = $this->config['flowtriq-api-key'] ?? '';
        if (! empty($api_key)) {
            $client = $client->withHeaders([
                'X-API-Key' => $api_key,
            ]);
        }

        $res = $client->post($url, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $alert_data['msg'] ?? '', $data);
    }

    /** @return array<string, array<int|string, mixed>> */
    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'flowtriq-url',
                    'descr' => 'Flowtriq webhook endpoint URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'API Key',
                    'name' => 'flowtriq-api-key',
                    'descr' => 'Flowtriq API key (optional)',
                    'type' => 'password',
                ],
            ],
            'validation' => [
                'flowtriq-url' => 'required|url',
                'flowtriq-api-key' => 'string',
            ],
        ];
    }
}
