<?php
/* LibreNMS
 *
 * Copyright (C) 2020 Chris Friesen <chris.friesen@virtechsystems.com>
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

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;

class Splunk extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        if (! empty($this->config)) {
            $opts['splunk_host'] = $this->config['Splunk-host'];
            $opts['splunk_port'] = $this->config['Splunk-port'];
        }

        return $this->contactSplunk($obj, $opts);
    }

    public function contactSplunk($obj, $opts)
    {
        $splunk_host = '127.0.0.1';
        $splunk_port = 514;
        $severity = 6; // Default severity is 6 (Informational)
        $device = device_by_id_cache($obj['device_id']); // for event logging

        if (! empty($opts['splunk_host'])) {
            if (preg_match('/[a-zA-Z]/', $opts['splunk_host'])) {
                $splunk_host = gethostbyname($opts['splunk_host']);
                if ($splunk_host === $opts['splunk_host']) {
                    log_event('Alphanumeric hostname found but does not resolve to an IP.', $device, 'poller', 5);

                    return false;
                }
            } elseif (filter_var($opts['splunk_host'], FILTER_VALIDATE_IP)) {
                $splunk_host = $opts['splunk_host'];
            } else {
                log_event('Splunk host is not a valid IP: ' . $opts['splunk_host'], $device, 'poller', 5);

                return false;
            }
        } else {
            log_event('Splunk host is empty.', $device, 'poller');
        }
        if (! empty($opts['splunk_port']) && preg_match("/^\d+$/", $opts['splunk_port'])) {
            $splunk_port = $opts['splunk_port'];
        } else {
            log_event('Splunk port is not an integer.', $device, 'poller', 5);
        }

        switch ($obj['severity']) {
            case 'critical':
                $severity = 2;
                break;
            case 'warning':
                $severity = 4;
                break;
        }

        switch ($obj['state']) {
            case AlertState::RECOVERED:
                $severity = 6;
                break;
            case AlertState::ACKNOWLEDGED:
                $severity = 6;
                break;
        }

        $ignore = ['template', 'contacts', 'rule', 'string', 'debug', 'faults', 'builder', 'transport', 'alert', 'msg', 'transport_name'];
        $splunk_prefix = '<' . $severity . '> ';
        foreach ($obj as $key => $val) {
            if (in_array($key, $ignore)) {
                continue;
            }

            $splunk_prefix .= $key . '="' . $val . '", ';
        }

        $ignore = ['attribs', 'vrf_lite_cisco', 'community', 'authlevel', 'authname', 'authpass', 'authalgo', 'cryptopass', 'cryptoalgo', 'snmpver', 'port'];
        foreach ($device as $key => $val) {
            if (in_array($key, $ignore)) {
                continue;
            }

            $splunk_prefix .= 'device_' . $key . '="' . $val . '", ';
        }
        $splunk_prefix = substr($splunk_prefix, 0, -1);

        if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false) {
            log_event('socket_create() failed: reason: ' . socket_strerror(socket_last_error()), $device, 'poller', 5);

            return false;
        } else {
            if (! empty($obj['faults'])) {
                foreach ($obj['faults'] as $k => $v) {
                    $splunk_msg = $splunk_prefix . ' - ' . $v['string'];
                    socket_sendto($socket, $splunk_msg, strlen($splunk_msg), 0, $splunk_host, $splunk_port);
                }
            } else {
                $splunk_msg = $splunk_prefix;
                socket_sendto($socket, $splunk_msg, strlen($splunk_msg), 0, $splunk_host, $splunk_port);
            }
            socket_close($socket);
        }

        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Host',
                    'name' => 'Splunk-host',
                    'descr' => 'Splunk Host',
                    'type' => 'text',
                ],
                [
                    'title' => 'UDP Port',
                    'name' => 'Splunk-port',
                    'descr' => 'Splunk Port',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'Splunk-host' => 'required|string',
                'Splunk-port' => 'required|numeric',
            ],
        ];
    }
}
