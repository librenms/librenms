<?php
/* LibreNMS
 *
 * Copyright (C) 2017 Paul Blasquez <pblasquez@gmail.com>
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

class Syslog extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        if (! empty($this->config)) {
            $opts['syslog_host'] = $this->config['syslog-host'];
            $opts['syslog_port'] = $this->config['syslog-port'];
            $opts['syslog_facility'] = $this->config['syslog-facility'];
        }

        return $this->contactSyslog($obj, $opts);
    }

    public function contactSyslog($obj, $opts)
    {
        $syslog_host = '127.0.0.1';
        $syslog_port = 514;
        $state = 'Unknown';
        $facility = 24; // Default facility is 3 * 8 (daemon)
        $severity = 6; // Default severity is 6 (Informational)
        $sev_txt = 'OK';
        $device = device_by_id_cache($obj['device_id']); // for event logging

        if (! empty($opts['syslog_facility']) && preg_match("/^\d+$/", $opts['syslog_facility'])) {
            $facility = (int) $opts['syslog_facility'] * 8;
        } else {
            log_event('Syslog facility is not an integer: ' . $opts['syslog_facility'], $device, 'poller', 5);
        }
        if (! empty($opts['syslog_host'])) {
            if (preg_match('/[a-zA-Z]/', $opts['syslog_host'])) {
                $syslog_host = gethostbyname($opts['syslog_host']);
                if ($syslog_host === $opts['syslog_host']) {
                    log_event('Alphanumeric hostname found but does not resolve to an IP.', $device, 'poller', 5);

                    return false;
                }
            } elseif (filter_var($opts['syslog_host'], FILTER_VALIDATE_IP)) {
                $syslog_host = $opts['syslog_host'];
            } else {
                log_event('Syslog host is not a valid IP: ' . $opts['syslog_host'], $device, 'poller', 5);

                return false;
            }
        } else {
            log_event('Syslog host is empty.', $device, 'poller');
        }
        if (! empty($opts['syslog_port']) && preg_match("/^\d+$/", $opts['syslog_port'])) {
            $syslog_port = $opts['syslog_port'];
        } else {
            log_event('Syslog port is not an integer.', $device, 'poller', 5);
        }

        switch ($obj['severity']) {
            case 'critical':
                $severity = 2;
                $sev_txt = 'Critical';
                break;
            case 'warning':
                $severity = 4;
                $sev_txt = 'Warning';
                break;
        }

        switch ($obj['state']) {
            case AlertState::RECOVERED:
                $state = 'OK';
                $severity = 6;
                break;
            case AlertState::ACTIVE:
                $state = $sev_txt;
                break;
            case AlertState::ACKNOWLEDGED:
                $state = 'Acknowledged';
                $severity = 6;
                break;
        }

        $priority = $facility + $severity;

        $syslog_prefix = '<'
            . $priority
            . '> '
            . date('M d H:i:s ')
            . gethostname()
            . ' librenms'
            . '['
            . $obj['device_id']
            . ']: '
            . $obj['hostname']
            . ': ['
            . $state
            . '] '
            . $obj['name'];

        if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false) {
            log_event('socket_create() failed: reason: ' . socket_strerror(socket_last_error()), $device, 'poller', 5);

            return false;
        } else {
            if (! empty($obj['faults'])) {
                foreach ($obj['faults'] as $k => $v) {
                    $syslog_msg = $syslog_prefix . ' - ' . $v['string'];
                    socket_sendto($socket, $syslog_msg, strlen($syslog_msg), 0, $syslog_host, $syslog_port);
                }
            } else {
                $syslog_msg = $syslog_prefix;
                socket_sendto($socket, $syslog_msg, strlen($syslog_msg), 0, $syslog_host, $syslog_port);
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
                    'name' => 'syslog-host',
                    'descr' => 'Syslog Host',
                    'type' => 'text',
                ],
                [
                    'title' => 'Port',
                    'name' => 'syslog-port',
                    'descr' => 'Syslog Port',
                    'type' => 'text',
                ],
                [
                    'title' => 'Facility',
                    'name' => 'syslog-facility',
                    'descr' => 'Syslog Facility',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'syslog-host' => 'required|string',
                'syslog-port' => 'required|numeric',
                'syslog-facility' => 'required|string',
            ],
        ];
    }
}
