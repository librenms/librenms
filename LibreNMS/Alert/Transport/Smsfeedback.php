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
 * @author Barry O'Donovan <barry@lightnet.ie>
 * @copyright 2017 Barry O'Donovan, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Smsfeedback extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $smsfeedback_opts['user'] = $this->config['smsfeedback-user'];
        $smsfeedback_opts['token'] = $this->config['smsfeedback-pass'];
        $smsfeedback_opts['sender'] = $this->config['smsfeedback-sender'];
        $smsfeedback_opts['to'] = $this->config['smsfeedback-mobiles'];

        return $this->contactsmsfeedback($obj, $smsfeedback_opts);
    }

    public static function contactsmsfeedback($obj, $opts)
    {
        $params = [
            'login' => $opts['user'],
            'pass' => md5($opts['token']),
            'phone' => $opts['to'],
            'text' => $obj['title'],
            'sender' => $opts['sender'],
        ];
        $url = 'http://' . $opts['user'] . ':' . $opts['token'] . '@' . 'api.smsfeedback.ru/messages/v2/send/?' . http_build_query($params);
        $curl = curl_init($url);

        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $ret = curl_exec($curl);
        if (substr($ret, 0, 8) == 'accepted') {
            return true;
        }
    }

    public static function configTemplate()
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
                    'type' => 'text',
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
                'smsfeedback-user'    => 'required|string',
                'smsfeedback-pass'    => 'required|string',
                'smsfeedback-mobiles' => 'required',
                'smsfeedback-sender' => 'required|string',
            ],
        ];
    }
}
