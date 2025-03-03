<?php
/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>
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
 * ilert Transport
 *
 * @author t.plueer <t.plueer@first-colo.net>, l.prosch <l.prosch@first-colo.net>
 * @copyright 2024 firstcolo
 * @copyright 2022 f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Ilert extends Transport
{
    public function name(): string
    {
        return 'ilert';
    }

    public function deliverAlert(array $alert_data): bool
    {
        if ($alert_data['state'] == AlertState::RECOVERED) {
            $event_type = 'RESOLVE';
        } elseif ($alert_data['state'] == AlertState::ACKNOWLEDGED) {
            $event_type = 'ACCEPT';
        } else {
            $event_type = 'ALERT';
        }

        $data = [
            'integrationKey' => $this->config['integration-key'],
            'eventType' => $event_type,
            'alertKey' => (string) $alert_data['alert_id'],
            'summary' => $alert_data['title'],
            'details' => $alert_data['msg'],
            'priority' => ($alert_data['severity'] == 'Critical') ? 'HIGH' : 'LOW',
        ];

        $tmp_msg = json_decode($alert_data['msg'], true);
        if (isset($tmp_msg['summary']) && isset($tmp_msg['details'])) {
            $data = array_merge($data, $tmp_msg);
        }

        $headers = [
            'Content-Type' => 'application/json',
            'Content-Length' => strlen(json_encode($data)),
        ];

        $res = Http::client()
            ->withHeaders($headers)
            ->post('https://api.ilert.com/api/events', $data);

        if ($res->successful() && $res->status() == '202') {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $alert_data['msg'], $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Integration Key',
                    'name' => 'integration-key',
                    'descr' => 'ilert Integration Key',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'integration-key' => 'required|string',
            ],
        ];
    }
}
