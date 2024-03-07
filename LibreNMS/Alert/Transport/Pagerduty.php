<?php
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
 * PagerDuty Generic-API Transport
 *
 * @author f0o <f0o@devilcode.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Pagerduty extends Transport
{
    protected string $name = 'PagerDuty';

    public function deliverAlert(array $alert_data): bool
    {
        $event_action = match ($alert_data['state']) {
            AlertState::RECOVERED => 'resolve',
            AlertState::ACKNOWLEDGED => 'acknowledge',
            default => 'trigger'
        };

        $safe_message = strip_tags($alert_data['msg']) ?: 'Test';
        $message = array_filter(explode("\n", $safe_message), function ($value): bool {
            return strlen($value) > 0;
        });
        $data = [
            'routing_key' => $this->config['service_key'],
            'event_action' => $event_action,
            'dedup_key' => (string) $alert_data['alert_id'],
            'payload' => [
                'custom_details' => ['message' => $message],
                'group' => (string) \DeviceCache::get($alert_data['device_id'])->groups->pluck('name'),
                'source' => $alert_data['hostname'],
                'severity' => $alert_data['severity'],
                'summary' => ($alert_data['name'] ? $alert_data['name'] . ' on ' . $alert_data['hostname'] : $alert_data['title']),
            ],
        ];

        // EU service region
        $url = match ($this->config['region']) {
            'EU' => 'https://events.eu.pagerduty.com/v2/enqueue',
            'US' => 'https://events.pagerduty.com/v2/enqueue',
            default => $this->config['custom-url'],
        };

        $res = Http::client()->post($url, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), implode(PHP_EOL, $message), $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Service Region',
                    'name' => 'region',
                    'descr' => 'Service Region of the PagerDuty account',
                    'type' => 'select',
                    'options' => [
                        'EU' => 'EU',
                        'US' => 'US',
                        'Custom URL' => 'CUSTOM',
                    ],
                ],
                [
                    'title' => 'Routing Key',
                    'type' => 'text',
                    'name' => 'service_key',
                ],
                [
                    'title' => 'Custom API URL',
                    'type' => 'text',
                    'name' => 'custom-url',
                    'descr' => 'Custom PagerDuty API URL',
                ],
            ],
            'validation' => [
                'region' => 'in:EU,US,CUSTOM',
                'custom-url' => 'url',
            ],
        ];
    }
}
