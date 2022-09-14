<?php

/* Copyright (C) 2015 Daniel Preussker <f0o@devilcode.org>
 ** This program is free software: you can redistribute it and/or modify
 ** it under the terms of the GNU General Public License as published by
 ** the Free Software Foundation, either version 3 of the License, or
 ** (at your option) any later version.
 **
 ** This program is distributed in the hope that it will be useful,
 ** but WITHOUT ANY WARRANTY; without even the implied warranty of
 ** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 ** GNU General Public License for more details.
 **
 ** You should have received a copy of the GNU General Public License
 ** along with this program.  If not, see <https://www.gnu.org/licenses/>. */

/**
 ** SNMP Trap Transport
 **
 ** @author Cloud Delivery Team <ttc-cloud-delivery@millicom.com>
 ** @copyright 2022 Cloud Delivery Team, Tigo Technology Center
 ** @license GPL
 **/

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Config;

class Snmptrap extends Transport
{
    /**
     * @param  array  $obj
     * @param  array  $opts
     */
    public function deliverAlert($obj, $opts)
    {
        $host = $this->config['snmptrap-destination-host'];
        if (! empty($this->config['snmptrap-destination-port'])) {
            $port = $this->config['snmptrap-destination-port'];
        } else {
            /* Default SNMP trap port */
            $port = '162';
        }
        $transport = $this->config['snmptrap-transport'];
        $trapdefinition = $this->config['snmptrap-definition'];
        $pdu = $this->config['snmptrap-pdu'];
        $community = $this->config['snmptrap-community'];
        $binary = implode([Config::get('snmptrap')]);
        $mibdir = $this->config['mib-dir'];

        /**
         * @param  array  $obj
         */
        return $this->contactSnmptrap($binary, $mibdir, $transport, $host, $port, $community, $trapdefinition, $pdu, $obj);
    }

    /**
     * Returns if the call was successful
     *
     * @return bool
     */
    private function contactSnmptrap(string $binary, string $mibdir, string $transport, string $host, string $port, string $community, string $trapdefinition, string $pdu, array $obj)
    {
        $binary_opts = '';
        switch ($pdu) {
            case 'TRAPv2':
                 $binary_opts = '-v 2c';
                break;
            case 'INFORM':
                $binary_opts = '-v 2c -Ci';
                break;
            default:
                echo 'This should not happen!!!';
                break;
        }

        $msgsingle = preg_replace('~\R~', ' ', $obj['msg']);

        putenv('SNMP_PERSISTENT_FILE=/tmp/snmpapp.conf.$USER');

        //exec('/usr/bin/echo'
        exec($binary
            . ' ' . $binary_opts
            . ' ' . '-M +' . $mibdir
            . ' -c ' . $community
            . ' ' . $transport . ':' . $host . ':' . $port
            . ' ' . '\"\"' . ' ' . $trapdefinition . ' '
            . $msgsingle, $output, $cmdexitcode
        );

        //var_dump($output);
        if ($cmdexitcode == 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Destination host',
                    'name' => 'snmptrap-destination-host',
                    'descr' => 'Hostname or IP of the host that will receive the trap.',
                    'type' => 'text',
                ],
                [
                    'title' => 'Destination port',
                    'name' => 'snmptrap-destination-port',
                    'descr' => 'Port to be used. Defaults to 162 when not specified.',
                    'type' => 'text',
                ],
                [
                    'title' => 'SNMP Trap transport',
                    'name' => 'snmptrap-transport',
                    'descr' => 'UDP or TCP, UDP is default.',
                    'type' => 'select',
                    'options' => [
                        'UDP' => 'UDP',
                        'TCP' => 'TCP',
                    ],
                ],
                [
                    'title' => 'Community',
                    'name' => 'snmptrap-community',
                    'descr' => 'SNMP community',
                    'type' => 'text',
                ],
                [
                    'title' => 'Trap Definition',
                    'name' => 'snmptrap-definition',
                    'descr' => 'For v2c it should include sysUpTime and trap OID',
                    'type' => 'text',
                ],
                [
                    'title' => 'PDU',
                    'name' => 'snmptrap-pdu',
                    'descr' => 'Type of message to send',
                    'type' => 'select',
                    'options' => [
                        'TRAPv2' => 'TRAPv2',
                        'INFORM' => 'INFORM',
                    ],
                ],
                [
                    'title' => 'Binary path',
                    'name' => 'snmptrap-path',
                    'descr' => 'snmptrap binary path',
                    'type' => 'text',
                ],
                [
                    'title' => 'MIB file path',
                    'name' => 'mib-dir',
                    'descr' => 'Directory from where to load the MIB entities. Yes, we want to do it properly :)',
                    'type' => 'text',
                ],
            ],
            'validation' => [
                'snmptrap-destination-host' => 'required|string',
                'snmptrap-destination-port' => 'numeric',
                'snmptrap-transport' => 'in:UDP,TCP',
                'snmptrap-community' => 'required|string',
                'snmptrap-definition' => 'required|string',
                'snmptrap-pdu' => 'in:TRAPv2,INFORM',
                'snmptrap-path' => 'required|string',
                'mib-dir' => 'required|string',
            ],
        ];
    }
}
