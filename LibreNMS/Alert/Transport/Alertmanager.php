<?php
/* Copyright (C) 2019 LibreNMS
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
 * Alertmanager Transport
 * @copyright 2019 LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */
namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Config;

class Alertmanager extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $alertmanager_opts = $this->parseUserOptions($this->config['alertmanager-options']);
        $alertmanager_opts['url'] = $this->config['alertmanager-url'];

        return $this->contactAlertmanager($obj, $alertmanager_opts);
    }

    public static function contactAlertmanager($obj, $api)
    {
        if ($obj['state'] == 0) {
            $alertmanager_status = 'endsAt';
        } else {
            $alertmanager_status = 'startsAt';
        }
        $gen_url          = (Config::get('base_url') . 'device/device=' . $obj['device_id']);
        $host             = ($api['url'] . '/api/v2/alerts');
        $curl             = curl_init();
        $alertmanager_msg = strip_tags($obj['msg']);
        $data             = [[
            $alertmanager_status => date("c"),
            'generatorURL' => $gen_url,
            'annotations' => [
                'summary' => $obj['name'],
                'title' => $obj['title'],
                'description' => $alertmanager_msg,
            ],
            'labels' => [
                    'alertname' => $obj['name'],
                    'severity' => $obj['severity'],
                    'instance' => $obj['hostname'],
                ],
        ]];

        unset($api['url']);
        foreach ($api as $label => $value) {
            $data[0]['labels'][$label] = $value;
        };

        $alert_message = json_encode($data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_URL, $host);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $alert_message);

        $ret  = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 200) {
            return 'HTTP Status code ' . $code;
        }
        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Alertmanager URL',
                    'name' => 'alertmanager-url',
                    'descr' => 'Alertmanager Webhook URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'Alertmanager Options',
                    'name' => 'alertmanager-options',
                    'descr' => 'Alertmanager Options',
                    'type' => 'textarea',
                ]
            ],
            'validation' => [
                'alertmanager-url' => 'required|url',
            ]
        ];
    }
}
