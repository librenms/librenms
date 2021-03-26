<?php

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;

class Gaia extends \LibreNMS\OS implements OSPolling
{
    public function pollOS()
    {
        //#############
        // Create firewall active connections rrd
        //#############
        $connections = snmp_get($this->getDeviceArray(), 'fwNumConn.0', '-OQv', 'CHECKPOINT-MIB');

        if (is_numeric($connections)) {
            $rrd_def = RrdDefinition::make()->addDataset('NumConn', 'GAUGE', 0);

            $fields = [
                'NumConn' => $connections,
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'gaia_connections', $tags, $fields);
            $this->enableGraph('gaia_connections');
        }

        //#############
        // Create firewall packets rrd
        //#############
        $mibs = 'CHECKPOINT-MIB';
        $oids = [
            'fwAccepted.0',
            'fwRejected.0',
            'fwDropped.0',
            'fwLogged.0',
        ];

        $data = snmp_get_multi($this->getDeviceArray(), $oids, '-OQUs', $mibs);

        $rrd_def = RrdDefinition::make()
            ->addDataset('accepted', 'DERIVE', 0)
            ->addDataset('rejected', 'DERIVE', 0)
            ->addDataset('dropped', 'DERIVE', 0)
            ->addDataset('logged', 'DERIVE', 0);

        $fields = [
            'accepted' => $data[0]['fwAccepted'],
            'rejected' => $data[0]['fwRejected'],
            'dropped' => $data[0]['fwDropped'],
            'logged' => $data[0]['fwLogged'],
        ];

        $tags = compact('rrd_def');
        data_update($this->getDeviceArray(), 'gaia_firewall_packets', $tags, $fields);
        $this->enableGraph('gaia_firewall_packets');
    }
}
