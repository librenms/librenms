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
 * API Transport
 *
 * @author ToeiRei <vbauer@stargazer.at>
 * @copyright 2017 ToeiRei, LibreNMS work based on the work of f0o. It's his work.
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Rocket extends Transport
{
    protected string $name = 'Rocket Chat';

    public function deliverAlert(array $alert_data): bool
    {
        $rocket_opts = $this->parseUserOptions($this->config['rocket-options']);

        $rocket_msg = strip_tags($alert_data['msg']);
        $data = [
            'attachments' => [
                0 => [
                    'fallback' => $rocket_msg,
                    'color' => self::getColorForState($alert_data['state']),
                    'title' => $alert_data['title'],
                    'text' => $rocket_msg,
                ],
            ],
            'channel' => $rocket_opts['channel'] ?? null,
            'username' => $rocket_opts['username'] ?? null,
            'icon_url' => $rocket_opts['icon_url'] ?? null,
            'icon_emoji' => $rocket_opts['icon_emoji'] ?? null,
        ];

        $res = Http::client()->post($this->config['rocket-url'], $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $rocket_msg, $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'rocket-url',
                    'descr' => 'Rocket.chat Webhook URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'Rocket.chat Options',
                    'name' => 'rocket-options',
                    'descr' => 'Rocket.chat Options',
                    'type' => 'textarea',
                ],
            ],
            'validation' => [
                'rocket-url' => 'required|url',
            ],
        ];
    }
}
