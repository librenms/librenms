<?php
/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>, Tyler Christiansen <code@tylerc.me>
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

/*
 * API Transport
 * @author Neil Lathwood, Configuration Services <neil@configuration.co.uk>
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class ZenDuty extends Transport
{
    protected string $name = 'ZenDuty';

    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['zenduty-url'];
        // If the alert has recovered set to resolved
        if ($alert_data['state'] == 0) {
            $alert_type = 'resolved';
        } elseif ($alert_data['state'] == 2) {
            $alert_type = 'acknowledged';
        } else {
            $alert_type = $alert_data['severity'];
        }
        // Set the standard data ZD expects to see
        $data = [
            'message' => $alert_data['title'],
            'alert_type' => $alert_type,
            'entity_id' => $alert_data['alert_id'],
            'urls' => [
                [
                    'link_url' => route('device', ['device' => $alert_data['device_id']]),
                    'link_text' => $alert_data['hostname'],
                ],
            ]
        ];

        $tmp_msg = json_decode($alert_data['msg'], true);
        if (isset($tmp_msg['message']) && isset($tmp_msg['summary'])) {
            $data = array_merge($data, $tmp_msg);
        } else {
            $data['summary'] = $alert_data['msg'];
        }

        $client = Http::client();

        $res = $client->withHeaders(
            [
                'Content-Type' => 'application/json',
            ]
        )->acceptJson()->post($url, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $data['message'], $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'ZenDuty WebHook',
                    'name' => 'zenduty-url',
                    'descr' => 'ZenDuty WebHook',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'zenduty-url' => 'required|url',
            ],
        ];
    }
}
