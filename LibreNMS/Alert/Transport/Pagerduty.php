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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * PagerDuty Generic-API Transport
 * @author f0o <f0o@devilcode.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

namespace LibreNMS\Alert\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use LibreNMS\Alert\Transport;
use Log;
use Validator;

class Pagerduty extends Transport
{
    public static $integrationKey = '2fc7c9f3c8030e74aae6';

    public function deliverAlert($obj, $opts)
    {
        if ($obj['state'] == 0) {
            $obj['event_type'] = 'resolve';
        } elseif ($obj['state'] == 2) {
            $obj['event_type'] = 'acknowledge';
        } else {
            $obj['event_type'] = 'trigger';
        }
        return $this->contactPagerduty($obj, $this->config);
    }

    /**
     * @param $obj
     * @param $config
     * @return bool|string
     */
    public function contactPagerduty($obj, $config)
    {
        $data = [
            'routing_key'  => $config['service_key'],
            'event_action' => $obj['event_type'],
            'dedup_key'    => (string)$obj['alert_id'],
            'payload'    => [
                'custom_details'  => substr(implode(PHP_EOL, array_column($obj['faults'], 'string')), 0, 1020) . '....' ?: 'Test',
                'source'   => $obj['hostname'],
                'severity' => $obj['severity'],
                'summary'  => ($obj['name'] ? $obj['name'] . ' on ' . $obj['hostname'] : $obj['title']),
            ],
        ];

        $url = 'https://events.pagerduty.com/v2/enqueue';
        $client = new Client();

        try {
            $result = $client->request('POST', $url, ['json' => $data]);

            if ($result->getStatusCode() == 202) {
                return true;
            }

            return $result->getReasonPhrase();
        } catch (GuzzleException $e) {
            return "Request to PagerDuty API failed. " . $e->getMessage();
        }
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Authorize',
                    'descr' => 'Alert with PagerDuty',
                    'type'  => 'oauth',
                    'icon'  => 'pagerduty-white.svg',
                    'class' => 'btn-success',
                    'url'   => 'https://connect.pagerduty.com/connect?vendor=' . self::$integrationKey . '&callback='
                ],
                [
                    'title' => 'Account',
                    'type'  => 'hidden',
                    'name'  => 'account',
                ],
                [
                    'title' => 'Service',
                    'type'  => 'hidden',
                    'name'  => 'service_name',
                ]
            ],
            'validation' => []
        ];
    }

    public function handleOauth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account' => 'alpha_dash',
            'service_key' => 'regex:/^[a-fA-F0-9]+$/',
            'service_name' => 'string',
        ]);

        if ($validator->fails()) {
            Log::error('Pagerduty oauth failed validation.', ['request' => $request->all()]);
            return false;
        }

        $config = json_encode($request->only('account', 'service_key', 'service_name'));

        if ($id = $request->get('id')) {
            return (bool)dbUpdate(['transport_config' => $config], 'alert_transports', 'transport_id=?', [$id]);
        } else {
            return (bool)dbInsert([
                'transport_name' => $request->get('service_name', 'PagerDuty'),
                'transport_type' => 'pagerduty',
                'is_default' => 0,
                'transport_config' => $config,
            ], 'alert_transports');
        }
    }
}
