<?php
/* Copyright (C) 2015 James Campbell <neokjames@gmail.com>
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

/* Copyright (C) 2015 Daniel Preussker <f0o@devilcode.org>
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
 * Pushover API Transport
 *
 * @author neokjames <neokjames@gmail.com>
 * @copyright 2015 neokjames, f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Pushover extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $options = $this->parseUserOptions($this->config['options']);

        $url = 'https://api.pushover.net/1/messages.json';
        $data = [];
        $data['token'] = $this->config['appkey'];
        $data['user'] = $this->config['userkey'];
        // Entities are html encoded so this will cause them to be displayed correctly in pushover alerts
        $data['html'] = '1';
        switch ($alert_data['severity']) {
            case 'critical':
                $data['priority'] = 1;
                if (! empty($options['sound_critical'])) {
                    $data['sound'] = $options['sound_critical'];
                }
                break;
            case 'warning':
                $data['priority'] = 1;
                if (! empty($options['sound_warning'])) {
                    $data['sound'] = $options['sound_warning'];
                }
                break;
        }
        switch ($alert_data['state']) {
            case AlertState::RECOVERED:
                $data['priority'] = 0;
                if (! empty($options['sound_ok'])) {
                    $data['sound'] = $options['sound_ok'];
                }
                break;
        }
        $data['title'] = $alert_data['title'];
        $data['message'] = $alert_data['msg'];
        if ($options) {
            $data = array_merge($data, $options);
        }

        $res = Http::client()->asForm()->post($url, $data);

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
                    'title' => 'Api Key',
                    'name' => 'appkey',
                    'descr' => 'Api Key',
                    'type' => 'password',
                ],
                [
                    'title' => 'User Key',
                    'name' => 'userkey',
                    'descr' => 'User Key',
                    'type' => 'password',
                ],
                [
                    'title' => 'Pushover Options',
                    'name' => 'options',
                    'descr' => 'Pushover options',
                    'type' => 'textarea',
                ],
            ],
            'validation' => [
                'appkey' => 'required',
                'userkey' => 'required',
            ],
        ];
    }
}
