<?php
/* Copyright (C) 2019 George Pantazis <gpant@eservices-greece.com>
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
 * Mattermost API Transport
 * @author George Pantazis <gpant@eservices-greece.com>
 * @copyright 2019 George Pantazis, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Mattermost extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $mattermost_opts = [
            'url' => $this->config['mattermost-url'],
            'username' => $this->config['mattermost-username'],
            'icon' => $this->config['mattermost-icon'],
            'channel' => $this->config['mattermost-channel'],
            'author_name' => $this->config['mattermost-author_name'],
        ];

        return $this->contactMattermost($obj, $mattermost_opts);
    }

    public static function contactMattermost($obj, $api)
    {
        $host = $api['url'];
        $curl = curl_init();
        $mattermost_msg = strip_tags($obj['msg']);
        $color = self::getColorForState($obj['state']);
        $data = [
            'attachments' => [
                0 => [
                    'fallback' => $mattermost_msg,
                    'color' => $color,
                    'title' => $obj['title'],
                    'text' => $obj['msg'],
                    'mrkdwn_in' => ['text', 'fallback'],
                    'author_name' => $api['author_name'],
                ],
            ],
            'channel' => $api['channel'],
            'username' => $api['username'],
            'icon_url' => $api['icon'],
        ];

        $device = device_by_id_cache($obj['device_id']);

        set_curl_proxy($curl);

        $httpheaders = ['Accept: application/json', 'Content-Type: application/json'];
        $alert_payload = json_encode($data);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheaders);
        curl_setopt($curl, CURLOPT_URL, $host);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $alert_payload);

        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 200) {
            d_echo('Mattermost Connection Error: ' . $ret);

            return 'HTTP Status code ' . $code;
        } else {
            d_echo('Mattermost message sent for ' . $device);

            return true;
        }
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Webhook URL',
                    'name' => 'mattermost-url',
                    'descr' => 'Mattermost Webhook URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'Channel',
                    'name' => 'mattermost-channel',
                    'descr' => 'Mattermost Channel',
                    'type' => 'text',
                ],
                [
                    'title' => 'Username',
                    'name' => 'mattermost-username',
                    'descr' => 'Mattermost Username',
                    'type' => 'text',
                ],
                [
                    'title' => 'Icon',
                    'name' => 'mattermost-icon',
                    'descr' => 'Icon URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'Author_name',
                    'name' => 'mattermost-author_name',
                    'descr' => 'Optional name used to identify the author',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'mattermost-url' => 'required|url',
                'mattermost-icon' => 'url',
            ],
        ];
    }
}
