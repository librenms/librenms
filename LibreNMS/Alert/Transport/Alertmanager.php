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

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;
use LibreNMS\Util\Url;

class Alertmanager extends Transport
{
    protected string $name = 'Alert Manager';

    public function deliverAlert(array $alert_data): bool
    {
        $url = $this->config['alertmanager-url'];
        $username = $this->config['alertmanager-username'];
        $password = $this->config['alertmanager-password'];

        $alertmanager_status = $alert_data['state'] == AlertState::RECOVERED ? 'endsAt' : 'startsAt';
        $alertmanager_msg = strip_tags((string) $alert_data['msg']);
        $data = [[
            $alertmanager_status => date('c'),
            'generatorURL' => Url::deviceUrl($alert_data['device_id']),
            'annotations' => [
                'summary' => $alert_data['name'],
                'title' => $alert_data['title'],
                'description' => $alertmanager_msg,
            ],
            'labels' => [
                'alertname' => $alert_data['name'],
                'severity' => $alert_data['severity'],
                'instance' => $alert_data['hostname'],
            ],
        ]];

        $alertmanager_opts = $this->parseUserOptions($this->config['alertmanager-options']);
        foreach ($alertmanager_opts as $label => $value) {
            if (str_starts_with((string) $label, 'stc_')) {
                // Static label: strip the stc_ prefix and use the value as-is
                $cleanLabel = substr((string) $label, 4);
                $data[0]['labels'][$cleanLabel] = strip_tags((string) $value);
            } else {
                // Dynamic label: try to resolve value from alert data, faults, or fall back to literal
                $resolved = $alert_data[$value] ?? current(array_filter(
                    array_column($alert_data['faults'] ?? [], $value),
                    fn ($v) => ! empty($v)
                )) ?: $value;

                $data[0]['labels'][$label] = strip_tags((string) $resolved);

                if (str_starts_with((string) $label, 'dyn_') && $data[0]['labels'][$label] == $value) {
                    unset($data[0]['labels'][$label]);
                }
            }
        }

        $urls = array_values(array_filter(array_map(trim(...), explode(',', (string) $url))));

        $client = Http::client()->timeout(2);

        $responses = $client->pool(fn (Pool $pool) => array_map(function (string $baseUrl) use ($pool, $username, $password, $data) {
            $req = $pool;
            if ($username !== '' && $password !== '') {
                $req = $req->withBasicAuth($username, $password);
            }

            return $req->post(rtrim($baseUrl, '/') . '/api/v2/alerts', $data);
        }, $urls));

        foreach ($responses as $res) {
            if ($res instanceof ConnectionException) {
                throw new AlertTransportDeliveryException(
                    $alert_data,
                    0,
                    $res->getMessage(),
                    $alertmanager_msg,
                    $data
                );
            }
            if (! $res->successful()) {
                throw new AlertTransportDeliveryException(
                    $alert_data,
                    $res->status(),
                    $res->body(),
                    $alertmanager_msg,
                    $data
                );
            }
        }

        return true;
    }

    public static function configTemplate(): array
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
