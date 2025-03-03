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

class Smsfeedback extends Transport
{
    protected string $name = 'SMSfeedback';

    public function deliverAlert(array $alert_data): bool
    {
        $url = 'http://api.smsfeedback.ru/messages/v2/send/';
        $params = [
            'phone' => $this->config['smsfeedback-mobiles'],
            'text' => $alert_data['title'],
            'sender' => $this->config['smsfeedback-sender'],
        ];

        $res = Http::client()
            ->withBasicAuth($this->config['smsfeedback-user'], $this->config['smsfeedback-pass'])
            ->get($url, $params);

        if ($res->successful() && str_starts_with($res->body(), 'accepted')) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $alert_data['title'], $params);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'User',
                    'name' => 'smsfeedback-user',
                    'descr' => 'smsfeedback User',
                    'type' => 'text',
                ],
                [
                    'title' => 'Password',
                    'name' => 'smsfeedback-pass',
                    'descr' => 'smsfeedback Password',
                    'type' => 'password',
                ],
                [
                    'title' => 'Mobiles',
                    'name' => 'smsfeedback-mobiles',
                    'descr' => 'smsfeedback Mobile number',
                    'type' => 'textarea',
                ],
                [
                    'title' => 'Sender',
                    'name' => 'smsfeedback-sender',
                    'descr' => 'smsfeedback sender name',
                    'type' => 'textarea',
                ],
            ],
            'validation' => [
                'smsfeedback-user' => 'required|string',
                'smsfeedback-pass' => 'required|string',
                'smsfeedback-mobiles' => 'required',
                'smsfeedback-sender' => 'required|string',
            ],
        ];
    }
}
