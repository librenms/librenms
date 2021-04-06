<?php
/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>, Tyler Christiansen <code@tylerc.me>
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

/*
 * API Transport
 * @author Tyler Christiansen <code@tylerc.me>
 * @copyright 2014 Daniel Preussker, Tyler Christiansen, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Hipchat extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $hipchat_opts = $this->parseUserOptions($this->config['hipchat-options']);
        $hipchat_opts['url'] = $this->config['hipchat-url'];
        $hipchat_opts['room_id'] = $this->config['hipchat-room-id'];
        $hipchat_opts['from'] = $this->config['hipchat-from-name'];

        return $this->contactHipchat($obj, $hipchat_opts);
    }

    public function contactHipchat($obj, $option)
    {
        $version = 1;
        if (stripos($option['url'], 'v2')) {
            $version = 2;
        }

        // Generate our URL from the base URL + room_id and the auth token if the version is 2.
        $url = $option['url'];
        if ($version == 2) {
            $url .= '/' . urlencode($option['room_id']) . '/notification?auth_token=' . urlencode($option['auth_token']);
        }

        $curl = curl_init();

        if (empty($obj['msg'])) {
            return 'Empty Message';
        }

        if (empty($option['message_format'])) {
            $option['message_format'] = 'text';
        }

        // Sane default of making the message color green if the message indicates
        // that the alert recovered.   If it rebooted, make it yellow.
        if (stripos($obj['msg'], 'recovered')) {
            $color = 'green';
        } elseif (stripos($obj['msg'], 'rebooted')) {
            $color = 'yellow';
        } else {
            if (empty($option['color']) || $option['color'] == 'u') {
                $color = 'red';
            } else {
                $color = $option['color'];
            }
        }

        $data[] = 'message=' . urlencode($obj['msg']);
        if ($version == 1) {
            $data[] = 'room_id=' . urlencode($option['room_id']);
        }
        $data[] = 'from=' . urlencode($option['from']);
        $data[] = 'color=' . urlencode($color);
        $data[] = 'notify=' . urlencode($option['notify']);
        $data[] = 'message_format=' . urlencode($option['message_format']);

        $data = implode('&', $data);
        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);
        $ret = curl_exec($curl);

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 200 && $code != 204) {
            var_dump("API '$url' returned Error");
            //var_dump('Params: ' . $message);
            var_dump('Return: ' . $ret);

            return 'HTTP Status code ' . $code;
        }

        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'API URL',
                    'name' => 'hipchat-url',
                    'descr' => 'Hipchat API URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'Room ID',
                    'name' => 'hipchat-room-id',
                    'descr' => 'Hipchat Room ID',
                    'type' => 'text',
                ],
                [
                    'title' => 'From Name',
                    'name' => 'hipchat-from-name',
                    'descr' => 'From Name',
                    'type' => 'text',
                ],
                [
                    'title' => 'Hipchat Options',
                    'name' => 'hipchat-options',
                    'descr' => 'Hipchat Options',
                    'type' => 'textarea',
                ],
            ],
            'validation' => [
                'hipchat-url' => 'required|url',
                'hipchat-room-id' => 'required|numeric',
            ],
        ];
    }
}
