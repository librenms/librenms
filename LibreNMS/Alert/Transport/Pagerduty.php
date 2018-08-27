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

use LibreNMS\Alert\Transport;

class Pagerduty extends Transport
{
    public function deliverAlert($obj, $opts)
    {

        if ($obj['state'] == 0) {
            $obj['event_type'] = 'resolve';
        } elseif ($obj['state'] == 2) {
            $obj['event_type'] = 'acknowledge';
        } else {
            $obj['event_type'] = 'trigger';
        }

        if (empty($this->config)) {
            return $this->deliverAlertOld($obj, $opts);
        }
        return $this->contactPagerduty($obj, $this->config);
    }

    public function deliverAlertOld($obj, $opts)
    {
        // This code uses legacy events for PD
        $protocol = array(
            'service_key' => $opts,
            'incident_key' => ($obj['id'] ? $obj['id'] : $obj['uid']),
            'description' => ($obj['name'] ? $obj['name'] . ' on ' . $obj['hostname'] : $obj['title']),
            'client' => 'LibreNMS',
        );

        foreach ($obj['faults'] as $fault => $data) {
            $protocol['details'][] = $data['string'];
        }
        $curl = curl_init();
        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_URL, 'https://events.pagerduty.com/generic/2010-04-15/create_event.json');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type' => 'application/json'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($protocol));
        $ret  = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 200) {
            var_dump("PagerDuty returned Error, retry later"); //FIXME: propper debuging
            return 'HTTP Status code ' . $code;
        }
        return true;
    }

    public function contactPagerduty($obj, $config)
    {
        foreach ($obj['faults'] as $fault => $data) {
            $fault .= $data['string'] . PHP_EOL;
        }
        $data = [
            'routing_key'  => $config['pagerduty-integrationkey'],
            'event_action' => $obj['event_type'],
            'dedup_key'    => $obj['uid'],
            'payload'    => [
                'summary'  => $fault,
                'source'   => $obj['hostname'],
                'severity' => $obj['severity'],
            ],
        ];
        $curl = curl_init();
        set_curl_proxy($curl);
        curl_setopt($curl, CURLOPT_URL, 'https://events.pagerduty.com/v2/enqueue');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-type'  => 'application/json',
            'Authorization' => "Token token={$config['pagerduty-apikey']}",
        ]);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        $ret = json_decode(curl_exec($curl), true);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($code != 200 && $code != 202) {
            var_dump("PagerDuty returned Error ({$ret['message']})"); //FIXME: propper debuging
            return 'HTTP Status code ' . $code;
        }
        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'API Key',
                    'name' => 'pagerduty-apikey',
                    'descr' => 'API Key',
                    'type' => 'text',
                ],
                [
                    'title' => 'Integration Key',
                    'name' => 'pagerduty-integrationkey',
                    'descr' => 'Integration Key',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'pagerduty-apikey'         => 'required|string',
                'pagerduty-integrationkey' => 'required|string|size:32'
            ]
        ];
    }
}
