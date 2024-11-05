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
 * PlaySMS API Transport
 *
 * @author f0o <f0o@librenms.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Playsms extends Transport
{
    protected string $name = 'playSMS';

    public function deliverAlert(array $alert_data): bool
    {
        $to = preg_split('/([,\r\n]+)/', $this->config['playsms-mobiles']);

        $url = str_replace('?app=ws', '', $this->config['playsms-url']); // remove old format
        $data = [
            'app' => 'ws',
            'op' => 'pv',
            'u' => $this->config['playsms-user'],
            'h' => $this->config['playsms-token'],
            'to' => implode(',', $to),
            'msg' => $alert_data['title'],
        ];
        if (! empty($this->config['playsms-from'])) {
            $data['from'] = $this->config['playsms-from'];
        }

        $res = Http::client()->get($url, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $data['msg'], $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'PlaySMS URL',
                    'name' => 'playsms-url',
                    'descr' => 'PlaySMS URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'User',
                    'name' => 'playsms-user',
                    'descr' => 'PlaySMS User',
                    'type' => 'text',
                ],
                [
                    'title' => 'Token',
                    'name' => 'playsms-token',
                    'descr' => 'PlaySMS Token',
                    'type' => 'password',
                ],
                [
                    'title' => 'From',
                    'name' => 'playsms-from',
                    'descr' => 'PlaySMS From',
                    'type' => 'text',
                ],
                [
                    'title' => 'Mobiles',
                    'name' => 'playsms-mobiles',
                    'descr' => 'PlaySMS Mobiles, can be new line or comma separated',
                    'type' => 'textarea',
                ],
            ],
            'validation' => [
                'playsms-url' => 'required|url',
                'playsms-user' => 'required|string',
                'playsms-token' => 'required|string',
                'playsms-mobiles' => 'required',
            ],
        ];
    }
}
