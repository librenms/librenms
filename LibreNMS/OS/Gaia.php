<?php

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;

class Gaia extends \LibreNMS\OS implements OSPolling
{
    public function pollOS()
    {
        $oids = ['fwLoggingHandlingRate.0', 'mgLSLogReceiveRate.0', 'fwNumConn.0', 'fwAccepted.0', 'fwRejected.0', 'fwDropped.0', 'fwLogged.0'];

        $data = snmp_get_multi($this->getDeviceArray(), $oids, '-OQUs', 'CHECKPOINT-MIB');

        //#############
        // Create firewall lograte/handlingrate rrd
        //#############
        if (is_numeric($data[0]['fwLoggingHandlingRate'])) {
            $rrd_def = RrdDefinition::make()->addDataset('fwlograte', 'GAUGE', 0);

            $fields = [
                'fwlograte' => $data[0]['fwLoggingHandlingRate'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'gaia_firewall_lograte', $tags, $fields);
            $this->enableGraph('gaia_firewall_lograte');
        }

        //#############
        // Create MGMT logserver lograte rrd
        //#############
        if (is_numeric($data[0]['mgLSLogReceiveRate'])) {
            $rrd_def = RrdDefinition::make()->addDataset('LogReceiveRate', 'GAUGE', 0);

            $fields = [
                'LogReceiveRate' => $data[0]['mgLSLogReceiveRate'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'gaia_logserver_lograte', $tags, $fields);
            $this->enableGraph('gaia_logserver_lograte');
        }

        //#############
        // Create firewall active connections rrd
        //#############
        if (is_numeric($data[0]['fwNumConn'])) {
            $rrd_def = RrdDefinition::make()->addDataset('NumConn', 'GAUGE', 0);

            $fields = [
                'NumConn' => $data[0]['fwNumConn'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'gaia_connections', $tags, $fields);
            $this->enableGraph('gaia_connections');
        }

        //#############
        // Create firewall packets rrd
        //#############
        if (is_numeric($data[0]['fwAccepted']) && is_numeric($data[0]['fwRejected']) && is_numeric($data[0]['fwDropped']) && is_numeric($data[0]['fwLogged'])) {
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
}
