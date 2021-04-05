<?php
/*Copyright (c) 2019 GitStoph <https://github.com/GitStoph>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details. */
/**
 * API Transport
 * @author GitStoph <https://github.com/GitStoph>
 * @copyright 2019 GitStoph
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Config;
use LibreNMS\Enum\AlertState;

class Alerta extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        $opts['url'] = $this->config['alerta-url'];
        $opts['environment'] = $this->config['environment'];
        $opts['apikey'] = $this->config['apikey'];
        $opts['alertstate'] = $this->config['alertstate'];
        $opts['recoverstate'] = $this->config['recoverstate'];

        return $this->contactAlerta($obj, $opts);
    }

    public function contactAlerta($obj, $opts)
    {
        $host = $opts['url'];
        $curl = curl_init();
        $text = strip_tags($obj['msg']);
        $severity = ($obj['state'] == AlertState::RECOVERED ? $opts['recoverstate'] : $opts['alertstate']);
        $deviceurl = (Config::get('base_url') . 'device/device=' . $obj['device_id']);
        $devicehostname = $obj['hostname'];
        $data = [
            'resource' => $devicehostname,
            'event' => $obj['name'],
            'environment' => $opts['environment'],
            'severity' => $severity,
            'service' => [$obj['title']],
            'group' => $obj['name'],
            'value' => $obj['state'],
            'text' => $text,
            'tags' => [$obj['title']],
            'attributes' => [
                'sysName' => $obj['sysName'],
                'sysDescr' => $obj['sysDescr'],
                'os' => $obj['os'],
                'type' => $obj['type'],
                'ip' => $obj['ip'],
                'uptime' => $obj['uptime_long'],
                'moreInfo' => '<a href=' . $deviceurl . '>' . $devicehostname . '</a>',
            ],
            'origin' => $obj['rule'],
            'type' => $obj['title'],
        ];
        $alert_message = json_encode($data);
        set_curl_proxy($curl);
        $headers = ['Content-Type: application/json', 'Authorization: Key ' . $opts['apikey']];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $host);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $alert_message);
        $ret = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 201) {
            var_dump("API '$host' returned Error");
            var_dump('Params: ' . $alert_message);
            var_dump('Return: ' . $ret);
            var_dump('Headers: ', $headers);

            return 'HTTP Status code ' . $code;
        }

        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'API Endpoint',
                    'name' => 'alerta-url',
                    'descr' => 'Alerta API URL',
                    'type' => 'text',
                ],
                [
                    'title' => 'Environment',
                    'name' => 'environment',
                    'descr' => 'An allowed environment from your alertad.conf.',
                    'type' => 'text',
                ],
                [
                    'title' => 'Api Key',
                    'name' => 'apikey',
                    'descr' => 'Your alerta api key with minimally write:alert permissions.',
                    'type' => 'text',
                ],
                [
                    'title' => 'Alert State',
                    'name' => 'alertstate',
                    'descr' => 'What severity you want Alerta to reflect when rule matches.',
                    'type' => 'text',
                ],
                [
                    'title' => 'Recover State',
                    'name' => 'recoverstate',
                    'descr' => 'What severity you want Alerta to reflect when rule unmatches/recovers.',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'alerta-url' => 'required|url',
                'apikey' => 'required|string',
            ],
        ];
    }
}
