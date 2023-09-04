<?php
/*Copyright (c) 2019 GitStoph <https://github.com/GitStoph>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details. */

/**
 * API Transport
 *
 * @author GitStoph <https://github.com/GitStoph>
 * @copyright 2019 GitStoph
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;
use LibreNMS\Util\Url;

class Alerta extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $severity = ($alert_data['state'] == AlertState::RECOVERED ? $this->config['recoverstate'] : $this->config['alertstate']);
        $data = [
            'resource' => $alert_data['display'],
            'event' => $alert_data['name'],
            'environment' => $this->config['environment'],
            'severity' => $severity,
            'service' => [$alert_data['title']],
            'group' => $alert_data['name'],
            'value' => $alert_data['state'],
            'text' => strip_tags($alert_data['msg']),
            'tags' => [$alert_data['title']],
            'attributes' => [
                'sysName' => $alert_data['sysName'],
                'sysDescr' => $alert_data['sysDescr'],
                'os' => $alert_data['os'],
                'type' => $alert_data['type'],
                'ip' => $alert_data['ip'],
                'uptime' => $alert_data['uptime_long'],
                'moreInfo' => '<a href=' . Url::deviceUrl($alert_data['device_id']) . '>' . $alert_data['display'] . '</a>',
            ],
            'origin' => $alert_data['rule'],
            'type' => $alert_data['title'],
        ];

        $res = Http::client()
            ->withHeaders([
                'Authorization' => 'Key ' . $this->config['apikey'],
            ])
            ->post($this->config['alerta-url'], $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $data['text'], $data);
    }

    public static function configTemplate(): array
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
                    'type' => 'password',
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
