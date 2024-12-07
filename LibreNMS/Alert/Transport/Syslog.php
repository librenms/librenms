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
use LibreNMS\Exceptions\AlertTransportDeliveryException;

class Syslog extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        return $this->contactSyslog($alert_data);
    }

    public function contactSyslog(array $alert_data): bool
    {
        $syslog_host = '127.0.0.1';
        $syslog_port = 514;
        $state = 'Unknown';
        $facility = 24; // Default facility is 3 * 8 (daemon)
        $severity = 6; // Default severity is 6 (Informational)
        $sev_txt = 'OK';

        if (! empty($this->config['syslog-facility'])) {
            $facility = (int) $this->config['syslog-facility'] * 8;
        }

        if (! empty($this->config['syslog-host'])) {
            if (preg_match('/[a-zA-Z]/', $this->config['syslog-host'])) {
                $syslog_host = gethostbyname($this->config['syslog-host']);
                if ($syslog_host === $this->config['syslog-host']) {
                    throw new AlertTransportDeliveryException($alert_data, 0, 'Hostname found but does not resolve to an IP.');
                }
            }
            $syslog_host = $this->config['syslog-host'];
        }

        if (! empty($this->config['syslog-port'])) {
            $syslog_port = $this->config['syslog-port'];
        }

        switch ($alert_data['severity']) {
            case 'critical':
                $severity = 2;
                $sev_txt = 'Critical';
                break;
            case 'warning':
                $severity = 4;
                $sev_txt = 'Warning';
                break;
        }

        switch ($alert_data['state']) {
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
            . '>'
            . date('M d H:i:s ')
            . gethostname()
            . ' librenms'
            . '['
            . $alert_data['device_id']
            . ']: '
            . $alert_data['hostname']
            . ': ['
            . $state
            . '] '
            . $alert_data['name'];

        if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false) {
            throw new AlertTransportDeliveryException($alert_data, 0, 'socket_create() failed: reason: ' . socket_strerror(socket_last_error()));
        }

        if (! empty($alert_data['faults'])) {
            foreach ($alert_data['faults'] as $fault) {
                $syslog_msg = $syslog_prefix . ' - ' . $fault['string'];
                socket_sendto($socket, $syslog_msg, strlen($syslog_msg), 0, $syslog_host, $syslog_port);
            }
        } else {
            $syslog_msg = $syslog_prefix;
            socket_sendto($socket, $syslog_msg, strlen($syslog_msg), 0, $syslog_host, $syslog_port);
        }
        socket_close($socket);

        return true;
    }

    public static function configTemplate(): array
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
                'syslog-host' => 'required|ip_or_hostname',
                'syslog-port' => 'required|integer|between:1,65536',
                'syslog-facility' => 'required|integer|between:0,23',
            ],
        ];
    }
}
