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
 * PlaySMS API Transport
 * @author f0o <f0o@librenms.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Playsms extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $playsms_opts['url'] = $this->config['playsms-url'];
        $playsms_opts['user'] = $this->config['playsms-user'];
        $playsms_opts['token'] = $this->config['playsms-token'];
        $playsms_opts['from'] = $this->config['playsms-from'];
        $playsms_opts['to'] = preg_split('/([,\r\n]+)/', $this->config['playsms-mobiles']);

        return $this->contactPlaysms($obj, $playsms_opts);
    }

    public static function contactPlaysms($obj, $opts)
    {
        $data = ['u' => $opts['user'], 'h' => $opts['token'], 'to' => implode(',', $opts['to']), 'msg' => $obj['title']];
        if (! empty($opts['from'])) {
            $data['from'] = $opts['from'];
        }
        $url = $opts['url'] . '&op=pv&' . http_build_query($data);
        $curl = curl_init($url);

        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code > 202) {
            var_dump($ret);

            return;
        }

        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'PlaySMS URL',
                    'name' => 'playsms-url',
                    'descr' => 'PlaySMS URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'User',
                    'name' => 'playsms-user',
                    'descr' => 'PlaySMS User',
                    'type' => 'text',
                ],
                [
                    'title' => 'Token',
                    'name' => 'playsms-token',
                    'descr' => 'PlaySMS Token',
                    'type' => 'text',
                ],
                [
                    'title' => 'From',
                    'name' => 'playsms-from',
                    'descr' => 'PlaySMS From',
                    'type' => 'text',
                ],
                [
                    'title' => 'Mobiles',
                    'name' => 'playsms-mobiles',
                    'descr' => 'PlaySMS Mobiles, can be new line or comma separated',
                    'type' => 'textarea',
                ],
            ],
            'validation' => [
                'playsms-url'     => 'required|url',
                'playsms-user'    => 'required|string',
                'playsms-token'   => 'required|string',
                'playsms-mobiles' => 'required',
            ],
        ];
    }
}
