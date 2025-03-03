<?php
/* Copyright (C) 2017 Celal Emre CICEK <celal.emre@opsgenie.com>
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
 * OpsGenie API Transport
 *
 * @author Celal Emre CICEK <celal.emre@opsgenie.com>
 * @copyright 2017 Celal Emre CICEK
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Opsgenie extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['genie-url'];

        $res = Http::client()->post($url, $alert_data);

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
                    'name' => 'genie-url',
                    'descr' => 'OpsGenie Webhook URL',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'genie-url' => 'required|url',
            ],
        ];
    }
}
