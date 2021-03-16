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
 * @author neokjames <neokjames@gmail.com>
 * @copyright 2015 neokjames, f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;

class Pushover extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $pushover_opts = $this->config;
        $pushover_opts['options'] = $this->parseUserOptions($this->config['options']);

        return $this->contactPushover($obj, $pushover_opts);
    }

    public function contactPushover($obj, $api)
    {
        $data = [];
        $data['token'] = $api['appkey'];
        $data['user'] = $api['userkey'];
        switch ($obj['severity']) {
            case 'critical':
                $data['priority'] = 1;
                if (! empty($api['options']['sound_critical'])) {
                    $data['sound'] = $api['options']['sound_critical'];
                }
                break;
            case 'warning':
                $data['priority'] = 1;
                if (! empty($api['options']['sound_warning'])) {
                    $data['sound'] = $api['options']['sound_warning'];
                }
                break;
        }
        switch ($obj['state']) {
            case AlertState::RECOVERED:
                $data['priority'] = 0;
                if (! empty($api['options']['sound_ok'])) {
                    $data['sound'] = $api['options']['sound_ok'];
                }
                break;
        }
        $data['title'] = $obj['title'];
        $data['message'] = $obj['msg'];
        if ($api['options']) {
            $data = array_merge($data, $api['options']);
        }
        $curl = curl_init();
        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_URL, 'https://api.pushover.net/1/messages.json');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 200) {
            var_dump('Pushover returned error'); //FIXME: proper debugging

            return 'HTTP Status code ' . $code;
        }

        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Api Key',
                    'name'  => 'appkey',
                    'descr' => 'Api Key',
                    'type'  => 'text',
                ],
                [
                    'title' => 'User Key',
                    'name'  => 'userkey',
                    'descr' => 'User Key',
                    'type'  => 'text',
                ],
                [
                    'title' => 'Pushover Options',
                    'name'  => 'options',
                    'descr' => 'Pushover options',
                    'type'  => 'textarea',
                ],
            ],
            'validation' => [
                'appkey' => 'required',
                'userkey' => 'required',
            ],
        ];
    }
}
