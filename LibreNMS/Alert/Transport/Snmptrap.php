<?php

/* Copyright (C) 2022 Cloud Delivery Team, Tigo Technology Center <ttc-cloud-delivery@millicom.com>
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
 * SNMP Trap Transport
 *
 * Sends LibreNMS alerts as SNMPv2c TRAPs or INFORMs to a northbound receiver.
 * Varbind content is driven by the alert template (see documentation).
 *
 * @author Cloud Delivery Team <ttc-cloud-delivery@millicom.com>
 * @copyright 2022 Cloud Delivery Team, Tigo Technology Center
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use App\Facades\LibrenmsConfig;
use LibreNMS\Alert\Transport;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use Symfony\Component\Process\Process;

class Snmptrap extends Transport
{
    protected string $name = 'SNMP Trap';

    public function deliverAlert(array $alert_data): bool
    {
        $host = $this->config['snmptrap-destination-host'];
        $port = $this->config['snmptrap-destination-port'] ?: '162';
        $transport = $this->config['snmptrap-transport'] ?: 'UDP';
        $trapdefinition = $this->config['snmptrap-definition'];
        $pdu = $this->config['snmptrap-pdu'] ?: 'TRAPv2';
        $community = $this->config['snmptrap-community'];
        $mibdir = $this->config['mib-dir'];
        $binary = LibrenmsConfig::get('snmptrap', '/usr/bin/snmptrap');

        $cmd = [$binary];

        if ($pdu === 'INFORM') {
            array_push($cmd, '-v', '2c', '-Ci');
        } else {
            // TRAPv2 (default)
            array_push($cmd, '-v', '2c');
        }

        // Additional MIB search path
        array_push($cmd, '-M', '+' . $mibdir);
        // Community string
        array_push($cmd, '-c', $community);
        // Target: transport:host:port
        $cmd[] = $transport . ':' . $host . ':' . $port;
        // sysUpTime OID (empty string = use agent uptime)
        $cmd[] = '';
        // Trap OID
        $cmd[] = $trapdefinition;

        // Append varbind arguments parsed from the alert template output
        foreach ($this->parseVarbinds($alert_data['msg'] ?? '') as $arg) {
            $cmd[] = $arg;
        }

        $process = new Process(
            $cmd,
            null,
            ['SNMP_PERSISTENT_FILE' => sys_get_temp_dir() . '/snmpapp.conf']
        );
        $process->run();

        if (! $process->isSuccessful()) {
            throw new AlertTransportDeliveryException(
                $alert_data,
                $process->getExitCode() ?? 1,
                $process->getErrorOutput(),
                $alert_data['msg'] ?? '',
                $this->config
            );
        }

        return true;
    }

    /**
     * Parse snmptrap varbind lines into individual command-line arguments.
     *
     * Each non-empty line produced by the alert template is expected to be:
     *   OID type value
     * where value may be a double-quoted string containing spaces.
     * Lines beginning with '#' are treated as comments and ignored.
     */
    private function parseVarbinds(string $msg): array
    {
        $args = [];
        foreach (explode("\n", $msg) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            foreach ($this->tokenizeLine($line) as $token) {
                $args[] = $token;
            }
        }

        return $args;
    }

    /**
     * Tokenize a single varbind line, respecting double-quoted strings.
     *
     * @return string[]
     */
    private function tokenizeLine(string $line): array
    {
        $tokens = [];
        $i = 0;
        $len = strlen($line);

        while ($i < $len) {
            // Skip whitespace
            while ($i < $len && ctype_space($line[$i])) {
                $i++;
            }
            if ($i >= $len) {
                break;
            }

            if ($line[$i] === '"') {
                // Quoted string — consume until closing quote
                $i++;
                $token = '';
                while ($i < $len && $line[$i] !== '"') {
                    if ($line[$i] === '\\' && $i + 1 < $len) {
                        $i++; // skip backslash, take next char literally
                    }
                    $token .= $line[$i++];
                }
                $i++; // skip closing quote
                $tokens[] = $token;
            } else {
                // Unquoted token — consume until whitespace
                $start = $i;
                while ($i < $len && ! ctype_space($line[$i])) {
                    $i++;
                }
                $tokens[] = substr($line, $start, $i - $start);
            }
        }

        return $tokens;
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Destination Host',
                    'name' => 'snmptrap-destination-host',
                    'descr' => 'Hostname or IP address of the trap receiver.',
                    'type' => 'text',
                ],
                [
                    'title' => 'Destination Port',
                    'name' => 'snmptrap-destination-port',
                    'descr' => 'UDP/TCP port on the receiver. Defaults to 162.',
                    'type' => 'text',
                    'default' => '162',
                ],
                [
                    'title' => 'Transport',
                    'name' => 'snmptrap-transport',
                    'descr' => 'Network transport protocol. UDP is standard.',
                    'type' => 'select',
                    'options' => [
                        'UDP' => 'UDP',
                        'TCP' => 'TCP',
                    ],
                    'default' => 'UDP',
                ],
                [
                    'title' => 'Community',
                    'name' => 'snmptrap-community',
                    'descr' => 'SNMPv2c community string.',
                    'type' => 'text',
                    'default' => 'public',
                ],
                [
                    'title' => 'Trap OID',
                    'name' => 'snmptrap-definition',
                    'descr' => 'Trap notification OID, e.g. LIBRENMS-NOTIFICATIONS-MIB::lnmsDefaultAlertEvent',
                    'type' => 'text',
                    'default' => 'LIBRENMS-NOTIFICATIONS-MIB::lnmsDefaultAlertEvent',
                ],
                [
                    'title' => 'PDU Type',
                    'name' => 'snmptrap-pdu',
                    'descr' => 'TRAPv2 sends one-way notifications; INFORM requires acknowledgement from the receiver.',
                    'type' => 'select',
                    'options' => [
                        'TRAPv2' => 'TRAPv2',
                        'INFORM' => 'INFORM',
                    ],
                    'default' => 'TRAPv2',
                ],
                [
                    'title' => 'MIB Directory',
                    'name' => 'mib-dir',
                    'descr' => 'Directory containing the MIB file(s) used by this transport.',
                    'type' => 'text',
                    'default' => '/opt/librenms/mibs/librenms',
                ],
            ],
            'validation' => [
                'snmptrap-destination-host' => 'required|string',
                'snmptrap-destination-port' => 'nullable|integer|between:1,65535',
                'snmptrap-transport' => 'in:UDP,TCP',
                'snmptrap-community' => 'required|string',
                'snmptrap-definition' => 'required|string',
                'snmptrap-pdu' => 'in:TRAPv2,INFORM',
                'mib-dir' => 'required|string',
            ],
        ];
    }
}
