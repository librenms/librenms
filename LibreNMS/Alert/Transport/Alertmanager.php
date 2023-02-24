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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>. */

/**
 * Alertmanager Transport
 *
 * @copyright 2019 LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Config;
use LibreNMS\Enum\AlertState;
use LibreNMS\Util\Proxy;

class Alertmanager extends Transport
{
    protected $name = 'Alert Manager';

    public function deliverAlert($obj, $opts)
    {
        $alertmanager_opts = $this->parseUserOptions($this->config['alertmanager-options']);
        $alertmanager_opts['url'] = $this->config['alertmanager-url'];

        $alertmanager_username = $this->config['alertmanager-username'];
        $alertmanager_password = $this->config['alertmanager-password'];

        return $this->contactAlertmanager($obj, $alertmanager_opts, $alertmanager_username, $alertmanager_password);
    }

    public function contactAlertmanager($obj, $api, string $username, string $password)
    {
        if ($obj['state'] == AlertState::RECOVERED) {
            $alertmanager_status = 'endsAt';
        } else {
            $alertmanager_status = 'startsAt';
        }
        $gen_url = (Config::get('base_url') . 'device/device=' . $obj['device_id']);
        $alertmanager_msg = strip_tags($obj['msg']);
        $data = [[
            $alertmanager_status => date('c'),
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

        $url = $api['url'];
        unset($api['url']);
        foreach ($api as $label => $value) {
            // To allow dynamic values
            if ((preg_match('/^extra_[A-Za-z0-9_]+$/', $label)) && (! empty($obj['faults'][1][$value]))) {
                $data[0]['labels'][$label] = $obj['faults'][1][$value];
            } else {
                $data[0]['labels'][$label] = $value;
            }
        }

        return $this->postAlerts($url, $data, $username, $password);
    }

    public static function postAlerts($url, $data, string $username, string $password)
    {
        $curl = curl_init();
        Proxy::applyToCurl($curl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 5000);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 5000);
        curl_setopt($curl, CURLOPT_POST, true);

        if ($username != '' && $password != '') {
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERNAME, $username);
            curl_setopt($curl, CURLOPT_PASSWORD, $password);
        }

        $alert_message = json_encode($data);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $alert_message);

        foreach (explode(',', $url) as $am) {
            $post_url = ($am . '/api/v2/alerts');
            curl_setopt($curl, CURLOPT_URL, $post_url);
            $ret = curl_exec($curl);
            if ($ret === false || curl_errno($curl)) {
                logfile("Failed to contact $post_url: " . curl_error($curl));
                continue;
            }
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($code == 200) {
                curl_close($curl);

                return true;
            }
        }

        $err = "Unable to POST to Alertmanager at $post_url .";

        if ($ret === false || curl_errno($curl)) {
            $err .= ' cURL error: ' . curl_error($curl);
        } else {
            $err .= ' HTTP status: ' . curl_getinfo($curl, CURLINFO_HTTP_CODE);
        }

        curl_close($curl);

        logfile($err);

        return $err;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Alertmanager URL(s)',
                    'name' => 'alertmanager-url',
                    'descr' => 'Alertmanager Webhook URL(s). Can contain comma-separated URLs',
                    'type' => 'text',
                ],
                [
                    'title' => 'Alertmanager Username',
                    'name' => 'alertmanager-username',
                    'descr' => 'Alertmanager Basic Username to authenticate to Alertmanager',
                    'type' => 'text',
                ],
                [
                    'title' => 'Alertmanager Password',
                    'name' => 'alertmanager-password',
                    'descr' => 'Alertmanager Basic Password to authenticate to Alertmanager',
                    'type' => 'password',
                ],
                [
                    'title' => 'Alertmanager Options',
                    'name' => 'alertmanager-options',
                    'descr' => 'Alertmanager Options. You can add any fixed string value or dynamic value from alert details (label name must start with extra_ and value must exists in alert details).',
                    'type' => 'textarea',
                ],
            ],
            'validation' => [
                'alertmanager-url' => 'required|string',
            ],
        ];
    }
}
