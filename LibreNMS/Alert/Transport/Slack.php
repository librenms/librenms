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
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Slack extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $slack_opts = $this->parseUserOptions($this->config['slack-options']);
        $slack_opts['url'] = $this->config['slack-url'];

        return $this->contactSlack($obj, $slack_opts);
    }

    public static function contactSlack($obj, $api)
    {
        $host = $api['url'];
        $curl = curl_init();
        $slack_msg = strip_tags($obj['msg']);
        $color = self::getColorForState($obj['state']);
        $data = [
            'attachments' => [
                0 => [
                    'fallback' => $slack_msg,
                    'color' => $color,
                    'title' => $obj['title'],
                    'text' => $slack_msg,
                    'mrkdwn_in' => ['text', 'fallback'],
                    'author_name' => $api['author_name'],
                ],
            ],
            'channel' => $api['channel'],
            'username' => $api['username'],
            'icon_emoji' => ':' . $api['icon_emoji'] . ':',
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
                    'name' => 'slack-url',
                    'descr' => 'Slack Webhook URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'Slack Options',
                    'name' => 'slack-options',
                    'descr' => 'Slack Options',
                    'type' => 'textarea',
                ],
            ],
            'validation' => [
                'slack-url' => 'required|url',
            ],
        ];
    }
}
