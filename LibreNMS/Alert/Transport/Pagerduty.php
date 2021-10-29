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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Util\Proxy;

class Pagerduty extends Transport
{
    protected $name = 'PagerDuty';

    public function deliverAlert($obj, $opts)
    {
        if ($obj['state'] == AlertState::RECOVERED) {
            $obj['event_type'] = 'resolve';
        } elseif ($obj['state'] == AlertState::ACKNOWLEDGED) {
            $obj['event_type'] = 'acknowledge';
        } else {
            $obj['event_type'] = 'trigger';
        }

        return $this->contactPagerduty($obj, $this->config);
    }

    /**
     * @param  array  $obj
     * @param  array  $config
     * @return bool|string
     */
    public function contactPagerduty($obj, $config)
    {
        $data = [
            'routing_key'  => $config['service_key'],
            'event_action' => $obj['event_type'],
            'dedup_key'    => (string) $obj['alert_id'],
            'payload'    => [
                'custom_details'  => strip_tags($obj['msg']) ?: 'Test',
                'group'   => (string) \DeviceCache::get($obj['device_id'])->groups->pluck('name'),
                'source'   => $obj['hostname'],
                'severity' => $obj['severity'],
                'summary'  => ($obj['name'] ? $obj['name'] . ' on ' . $obj['hostname'] : $obj['title']),
            ],
        ];

        // EU service region
        if ($config['region'] == 'EU') {
            $url = 'https://events.eu.pagerduty.com/v2/enqueue';
        }

        // US service region
        else {
            $url = 'https://events.pagerduty.com/v2/enqueue';
        }

        $client = new Client();

        $request_opts = ['json' => $data];
        $request_opts['proxy'] = Proxy::forGuzzle();

        try {
            $result = $client->request('POST', $url, $request_opts);

            if ($result->getStatusCode() == 202) {
                return true;
            }

            return $result->getReasonPhrase();
        } catch (GuzzleException $e) {
            return 'Request to PagerDuty API failed. ' . $e->getMessage();
        }
    }

    public static function configTemplate()
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
                    ],
                ],
                [
                    'title' => 'Routing Key',
                    'type'  => 'text',
                    'name'  => 'service_key',
                ],
            ],
            'validation' => [
                'region' => 'in:EU,US',
            ],
        ];
    }
}
