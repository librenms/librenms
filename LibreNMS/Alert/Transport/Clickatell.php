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
 * Clickatell REST-API Transport
 *
 * @author f0o <f0o@librenms.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Clickatell extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $url = 'https://platform.clickatell.com/messages/http/send';
        $params = [
            'apiKey' => $this->config['clickatell-token'],
            'to' => implode(',', preg_split('/([,\r\n]+)/', $this->config['clickatell-numbers'])),
            'content' => $alert_data['title'],
        ];

        $res = Http::client()->get($url, $params);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $alert_data['title'], $params);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Token',
                    'name' => 'clickatell-token',
                    'descr' => 'Clickatell Token',
                    'type' => 'password',
                ],
                [
                    'title' => 'Mobile Numbers',
                    'name' => 'clickatell-numbers',
                    'descr' => 'Enter mobile numbers, can be new line or comma separated',
                    'type' => 'textarea',
                ],
            ],
            'validation' => [
                'clickatell-token' => 'required|string',
                'clickatell-numbers' => 'required|string',
            ],
        ];
    }
}
