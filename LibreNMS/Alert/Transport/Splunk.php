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
use LibreNMS\Exceptions\AlertTransportDeliveryException;

class Splunk extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $splunk_host = empty($this->config['Splunk-host']) ? '127.0.0.1' : $this->config['Splunk-host'];
        $splunk_port = empty($this->config['Splunk-port']) ? 514 : $this->config['Splunk-port'];
        $severity = 6; // Default severity is 6 (Informational)
        $device = device_by_id_cache($alert_data['device_id']); // for event logging

        switch ($alert_data['severity']) {
            case 'critical':
                $severity = 2;
                break;
            case 'warning':
                $severity = 4;
                break;
        }

        switch ($alert_data['state']) {
            case AlertState::RECOVERED:
                $severity = 6;
                break;
            case AlertState::ACKNOWLEDGED:
                $severity = 6;
                break;
        }

        $ignore = ['template', 'contacts', 'rule', 'string', 'debug', 'faults', 'builder', 'transport', 'alert', 'msg', 'transport_name'];
        $splunk_prefix = '<' . $severity . '> ';
        foreach ($alert_data as $key => $val) {
            if (in_array($key, $ignore)) {
                continue;
            }

            $splunk_prefix .= $key . '="' . $val . '", ';
        }

        $ignore = ['attribs', 'community', 'authlevel', 'authname', 'authpass', 'authalgo', 'cryptopass', 'cryptoalgo', 'snmpver', 'port'];
        foreach ($device as $key => $val) {
            if (in_array($key, $ignore)) {
                continue;
            }

            $splunk_prefix .= 'device_' . $key . '="' . $val . '", ';
        }
        $splunk_prefix = substr($splunk_prefix, 0, -1);

        if (($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false) {
            throw new AlertTransportDeliveryException($alert_data, 0, 'socket_create() failed: reason: ' . socket_strerror(socket_last_error()));
        }

        if (! empty($alert_data['faults'])) {
            foreach ($alert_data['faults'] as $fault) {
                $splunk_msg = $splunk_prefix . ' - ' . $fault['string'];
                socket_sendto($socket, $splunk_msg, strlen($splunk_msg), 0, $splunk_host, $splunk_port);
            }
        } else {
            $splunk_msg = $splunk_prefix;
            socket_sendto($socket, $splunk_msg, strlen($splunk_msg), 0, $splunk_host, $splunk_port);
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
                    'name' => 'Splunk-host',
                    'descr' => 'Splunk Host',
                    'type' => 'text',
                    'default' => '127.0.0.1',
                ],
                [
                    'title' => 'UDP Port',
                    'name' => 'Splunk-port',
                    'descr' => 'Splunk Port',
                    'type' => 'text',
                    'default' => 514,
                ],
            ],
            'validation' => [
                'Splunk-host' => 'required|ip_or_hostname',
                'Splunk-port' => 'integer|between:1,65536',
            ],
        ];
    }
}
