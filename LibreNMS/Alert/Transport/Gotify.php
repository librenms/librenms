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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * Gotify API Transport
 * @author Viktoria Rei Bauer <vbauer@stargazer.at>
 * @copyright 2019 ToeiRei, f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */
namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Gotify extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        if (empty($this->config)) {
            return $this->deliverAlertOld($obj, $opts);
        }
        $gotify_server = $this->config['gotify_server'];
        $gotify_token  = $this->config['gotify_token'];
        return $this->contactGotify($obj, $gotify_server, $gotify_token);
    }

    private function deliverAlertOld($obj, $opts)
    {

        $this->contactGotify($obj, $gotify_server, $gotify_token);
        return true;
    }

    public function contactGotify($obj, $gotify_server, $gotify_token)
    {
        switch ($obj['severity']) {
            case "critical":
                $data['priority'] = 1;
                if (!empty($api['options']['sound_critical'])) {
                    $data['sound'] = $api['options']['sound_critical'];
                }
                break;
            case "warning":
                $data['priority'] = 1;
                if (!empty($api['options']['sound_warning'])) {
                    $data['sound'] = $api['options']['sound_warning'];
                }
                break;
        }
        switch ($obj['state']) {
            case 0:
                $data['priority'] = 0;
                if (!empty($api['options']['sound_ok'])) {
                    $data['sound'] = $api['options']['sound_ok'];
                }
                break;
        }
        $data['title']   = $obj['title'];
        $data['message'] = $obj['msg'];

        $curl            = curl_init();
        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_URL, $gotify_server . '/message?token=' . $gotify_token );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $ret  = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 200) {
            var_dump("Pushover returned error"); //FIXME: proper debugging
            return 'HTTP Status code ' . $code;
        }
        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Gotify Server',
                    'name'  => 'gotify_server',
                    'descr' => 'Gotify Server',
                    'type'  => 'text',
                ],
                [
                    'title' => 'Token',
                    'name'  => 'gotify_token',
                    'descr' => 'Token',
                    'type'  => 'text',
                ],
            ],
            'validation' => [
                'gotify_server' => 'required',
                'gotify_token'  => 'required',
            ]
        ];
    }
}

