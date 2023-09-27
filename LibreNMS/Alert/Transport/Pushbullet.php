<?php
/* Copyright (C) 2015 Daniel Preussker <f0o@librenms.org>
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
 * Pushbullet API Transport
 *
 * @author f0o <f0o@librenms.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Pushbullet extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        // Note: At this point it might be useful to iterate through $obj['contacts'] and send each of them a note ?
        $url = 'https://api.pushbullet.com/v2/pushes';
        $data = ['type' => 'note', 'title' => $alert_data['title'], 'body' => $alert_data['msg']];

        $res = Http::client()
            ->withToken($this->config['pushbullet-token'])
            ->post($url, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $alert_data['msg'], $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Access Token',
                    'name' => 'pushbullet-token',
                    'descr' => 'Pushbullet Access Token',
                    'type' => 'password',
                ],
            ],
            'validation' => [
                'pushbullet-token' => 'required|string',
            ],
        ];
    }
}
