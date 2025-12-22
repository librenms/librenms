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
        $msg_raw = (string) $alert_data['msg'];
        $decoded = json_decode($msg_raw, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // Valid JSON: use as-is for custom_details
            $custom_details = $decoded;
        } else {
            // Legacy behaviour: strip tags and split into non-empty lines
            $safe_message = strip_tags($msg_raw) ?: 'Test';
            $message = array_filter(explode("\n", $safe_message), fn ($value): bool => strlen($value) > 0);
            $custom_details = ['message' => $message];
        }
        $data = [
            'routing_key' => $this->config['service_key'],
            'event_action' => $event_action,
            'dedup_key' => (string) $alert_data['alert_id'],
            'payload' => [
                'custom_details' => $custom_details,
                'group' => (string) \DeviceCache::get($alert_data['device_id'])->groups->pluck('name'),
                'source' => $alert_data['hostname'],
                'severity' => $alert_data['severity'],
                'summary' => ($alert_data['title'] ?: $alert_data['name'] . ' on ' . $alert_data['hostname']),
                'class' => $alert_data['type'],
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

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), implode(PHP_EOL, $custom_details), $data);
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
