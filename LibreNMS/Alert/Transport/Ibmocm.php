<?php
/* Copyright (C) 2024 Jayna Rogers <rokinchikie@gmail.com>
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
 * IBM On Call Manager API Transport
 *
 * @author Jayna Rogers <rokinchikie@gmail.com>
 * @copyright Jayna Rogers 2024, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Ibmocm extends Transport
{
    protected string $name = 'IBM On Call Manager';

    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['ocm-url'];

        // Send HTTP POST request
        $res = Http::client()->post($url, $alert_data);

        // Check if request was successful
        if ($res->successful()) {
            return true;
        }

        // Throw an exception if the request failed
        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), '', $alert_data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'ocm-url',
                    'descr' => 'IBM On Call Manager Webhook URL',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'ocm-url' => 'required|url',
            ],
        ];
    }
}
