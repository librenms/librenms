<?php
/* Copyright (C) 2018 Paul Heinrichs <pdheinrichs@gmail.com>
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
 * @author Paul Heinrichs <pdheinrichs@gmail.com>
 * @copyright 2018 Paul Heinrichs
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

/**
 * The Hue API currently is fairly limited for alerts.
 * At it's current implementation we can send ['lselect' => "15 second flash", 'select' => "1 second flash"]
 * If a colour request is sent with it it will permenantly change the colour which is less than desired
 */
class Hue extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        // Don't alert on resolve at this time
        if ($alert_data['state'] == AlertState::RECOVERED) {
            return true;
        }

        $hue_user = $this->config['hue-user'];
        $url = $this->config['hue-host'] . "/api/$hue_user/groups/0/action";
        $duration = $this->config['hue-duration'];
        $data = ['alert' => $duration];

        $res = Http::client()
            ->acceptJson()
            ->put($url, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $duration, $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Host',
                    'name' => 'hue-host',
                    'descr' => 'Hue Host',
                    'type' => 'text',
                ],
                [
                    'title' => 'Hue User',
                    'name' => 'hue-user',
                    'descr' => 'Phillips Hue Host',
                    'type' => 'text',
                ],
                [
                    'title' => 'Duration',
                    'name' => 'hue-duration',
                    'descr' => 'Phillips Hue Duration',
                    'type' => 'select',
                    'options' => [
                        '1 Second' => 'select',
                        '15 Seconds' => 'lselect',
                    ],
                ],
            ],
            'validation' => [
                'hue-host' => 'required|string',
                'hue-user' => 'required|string',
                'hue-duration' => 'required|string',
            ],
        ];
    }
}
