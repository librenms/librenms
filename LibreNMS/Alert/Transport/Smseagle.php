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
 * SMSEagle API Transport
 *
 * @author Barry O'Donovan <barry@lightnet.ie>
 * @copyright 2017 Barry O'Donovan, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Smseagle extends Transport
{
    protected string $name = 'SMSEagle';

    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['smseagle-url'] . '/http_api/send_sms';
        if (! str_starts_with($url, 'http')) {
            $url = 'http://' . $url;
        }

        $params = [];

        // use token if available
        if (empty($this->config['smseagle-token'])) {
            $params['login'] = $this->config['smseagle-user'];
            $params['pass'] = $this->config['smseagle-pass'];
        } else {
            $params['access_token'] = $this->config['smseagle-token'];
        }

        $params['to'] = implode(',', preg_split('/([,\r\n]+)/', $this->config['smseagle-mobiles']));
        $params['message'] = $alert_data['title'];

        $res = Http::client()->get($url, $params);

        if ($res->successful() && str_starts_with($res->body(), 'OK')) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $params['message'], $params);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'SMSEagle Base URL',
                    'name' => 'smseagle-url',
                    'descr' => 'SMSEagle Host',
                    'type' => 'text',
                ],
                [
                    'title' => 'Access Token',
                    'name' => 'smseagle-token',
                    'descr' => 'SMSEagle Access Token',
                    'type' => 'password',
                ],
                [
                    'title' => 'User',
                    'name' => 'smseagle-user',
                    'descr' => 'SMSEagle User',
                    'type' => 'text',
                ],
                [
                    'title' => 'Password',
                    'name' => 'smseagle-pass',
                    'descr' => 'SMSEagle Password',
                    'type' => 'password',
                ],
                [
                    'title' => 'Mobiles',
                    'name' => 'smseagle-mobiles',
                    'descr' => 'SMSEagle Mobiles, can be new line or comma separated',
                    'type' => 'textarea',
                ],
            ],
            'validation' => [
                'smseagle-url' => 'required|url',
                'smseagle-token' => 'required_without:smseagle-user,smseagle-pass|string',
                'smseagle-user' => 'required_without:smseagle-token|string',
                'smseagle-pass' => 'required_without:smseagle-token|string',
                'smseagle-mobiles' => 'required',
            ],
        ];
    }
}
