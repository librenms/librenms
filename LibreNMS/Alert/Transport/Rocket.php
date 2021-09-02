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
 * @author ToeiRei <vbauer@stargazer.at>
 * @copyright 2017 ToeiRei, LibreNMS work based on the work of f0o. It's his work.
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Rocket extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $rocket_opts = $this->parseUserOptions($this->config['rocket-options']);
        $rocket_opts['url'] = $this->config['rocket-url'];

        return $this->contactRocket($obj, $rocket_opts);
    }

    public static function contactRocket($obj, $api)
    {
        $host = $api['url'];
        $curl = curl_init();
        $rocket_msg = strip_tags($obj['msg']);
        $color = self::getColorForState($obj['state']);
        $data = [
            'attachments' => [
                0 => [
                    'fallback' => $rocket_msg,
                    'color' => $color,
                    'title' => $obj['title'],
                    'text' => $rocket_msg,
                ],
            ],
            'channel' => $api['channel'],
            'username' => $api['username'],
            'icon_url' => $api['icon_url'],
            'icon_emoji' => $api['icon_emoji'],
        ];
        $alert_message = json_encode($data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_URL, $host);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $alert_message);

        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 200) {
            var_dump("API '$host' returned Error"); //FIXME: propper debuging
            var_dump('Params: ' . $alert_message); //FIXME: propper debuging
            var_dump('Return: ' . $ret); //FIXME: propper debuging

            return 'HTTP Status code ' . $code;
        }

        return true;
    }

    public static function configTemplate()
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
