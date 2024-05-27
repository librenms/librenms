<?php
/* Copyright (C) 2024 SIGNL4
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
 * API Transport
 *
 * @author SIGNL4 <hello@signl4.com>
 * @copyright 2024 SIGNL4
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Signl4 extends Transport
{
    protected string $name = 'SIGNL4';

    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['signl4-url'];

        $alert_status = match ($alert_data['state']) {
            AlertState::RECOVERED => 'resolved',
            AlertState::ACKNOWLEDGED => 'acknowledged',
            default => 'new'
        };

        $s4_data = [
            'X-S4-ExternalID' => (string) $alert_data['alert_id'],
            'X-S4-Status' => $alert_status,
            'Body' => $alert_data['alert_notes'],
        ];

        $data = array_merge($s4_data, $alert_data);

        $res = Http::client()->post($url, $data);

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
                    'name' => 'signl4-url',
                    'descr' => 'SIGNL4 webhook URL including team or integration secret.',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'signl4-url' => 'required|url',
            ],
        ];
    }
}
