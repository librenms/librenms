<?php

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\SnmpQuery;

class Purestorage extends \LibreNMS\OS implements OSPolling
{
    /**
     * Poll Pure Storage array metrics via SNMP
     */
    public function pollOS(DataStorageInterface $datastore): void
    {
        // Pure Storage SNMP metrics OIDs (from PURESTORAGE-MIB)
        // Using numeric OIDs directly since MIB object names don't exist in the device
        
        $metrics = [
            'pureArrayReadBandwidth'  => '.1.3.6.1.4.1.40482.4.1.0',    // bytes/sec
            'pureArrayWriteBandwidth' => '.1.3.6.1.4.1.40482.4.2.0',    // bytes/sec
            'pureArrayReadIOPS'       => '.1.3.6.1.4.1.40482.4.3.0',    // ops/sec
            'pureArrayWriteIOPS'      => '.1.3.6.1.4.1.40482.4.4.0',    // ops/sec
            'pureArrayReadLatency'    => '.1.3.6.1.4.1.40482.4.5.0',    // microseconds
            'pureArrayWriteLatency'   => '.1.3.6.1.4.1.40482.4.6.0',    // microseconds
        ];

        // Query all OIDs at once
        $oids_list = array_values($metrics);
        $snmp_data = snmp_get_multi_oid($this->getDeviceArray(), $oids_list);

        if (empty($snmp_data)) {
            echo "[Purestorage] No metrics found\n";
            return;
        }

        // Extract values from SNMP response
        $data = [];
        foreach ($metrics as $name => $oid) {
            if (isset($snmp_data[$oid])) {
                $value = $snmp_data[$oid];
                // Cast to integer, filtering out non-numeric values
                if (is_numeric($value)) {
                    $data[$name] = (int)$value;
                    echo "[Purestorage] $name = $value\n";
                } else {
                    echo "[Purestorage] WARNING: $name has non-numeric value: $value\n";
                }
            }
        }

        if (empty($data)) {
            echo "[Purestorage] No valid metrics returned from SNMP\n";
            return;
        }

        echo "[Purestorage] Polling " . count($data) . " metrics\n";

        // Store metrics in RRD files
        $this->storeBandwidth($datastore, $data);
        $this->storeIOPS($datastore, $data);
        $this->storeLatency($datastore, $data);
        
        // Enable graphs for display
        $this->enableGraph('purestorage_bandwidth');
        $this->enableGraph('purestorage_iops');
        $this->enableGraph('purestorage_latency');
    }

    /**
     * Store bandwidth metrics in RRD
     * Bandwidth is in bytes/second and will be converted to bits/second by the YAML RPN
     */
    private function storeBandwidth(DataStorageInterface $datastore, $data): void
    {
        $rrd_name = 'purestorage_bandwidth';
        
        $rrd_def = RrdDefinition::make()
            ->addDataset('read', 'GAUGE', 0, 125000000000)      // max 125 Gbps
            ->addDataset('write', 'GAUGE', 0, 125000000000);

        $read = isset($data['pureArrayReadBandwidth']) ? (int)$data['pureArrayReadBandwidth'] : 0;
        $write = isset($data['pureArrayWriteBandwidth']) ? (int)$data['pureArrayWriteBandwidth'] : 0;
        
        $fields = [
            'read'  => $read,
            'write' => $write,
        ];
        
        echo "[Purestorage] Bandwidth - read: $read, write: $write\n";
        
        $tags = ['rrd_def' => $rrd_def];
        $datastore->put($this->getDeviceArray(), $rrd_name, $tags, $fields);
        echo "[Purestorage] Stored bandwidth metrics\n";
    }

    /**
     * Store IOPS metrics in RRD
     * Operations per second (no conversion needed)
     */
    private function storeIOPS(DataStorageInterface $datastore, $data): void
    {
        $rrd_name = 'purestorage_iops';
        
        $rrd_def = RrdDefinition::make()
            ->addDataset('read', 'DERIVE', 0, 1000000000)        // max 1B ops/sec
            ->addDataset('write', 'DERIVE', 0, 1000000000);

        $read = isset($data['pureArrayReadIOPS']) ? (int)$data['pureArrayReadIOPS'] : 0;
        $write = isset($data['pureArrayWriteIOPS']) ? (int)$data['pureArrayWriteIOPS'] : 0;
        
        $fields = [
            'read'  => $read,
            'write' => $write,
        ];
        
        echo "[Purestorage] IOPS - read: $read, write: $write\n";
        
        $tags = ['rrd_def' => $rrd_def];
        $datastore->put($this->getDeviceArray(), $rrd_name, $tags, $fields);
        echo "[Purestorage] Stored IOPS metrics\n";
    }

    /**
     * Store latency metrics in RRD
     * Latency is in microseconds and will be converted to milliseconds by the YAML RPN
     */
    private function storeLatency(DataStorageInterface $datastore, $data): void
    {
        $rrd_name = 'purestorage_latency';
        
        $rrd_def = RrdDefinition::make()
            ->addDataset('read', 'GAUGE', 0, 1000000)            // max 1 second in µs
            ->addDataset('write', 'GAUGE', 0, 1000000);

        $read = isset($data['pureArrayReadLatency']) ? (int)$data['pureArrayReadLatency'] : 0;
        $write = isset($data['pureArrayWriteLatency']) ? (int)$data['pureArrayWriteLatency'] : 0;
        
        $fields = [
            'read'  => $read,
            'write' => $write,
        ];
        
        echo "[Purestorage] Latency - read: $read, write: $write\n";
        
        $tags = ['rrd_def' => $rrd_def];
        $datastore->put($this->getDeviceArray(), $rrd_name, $tags, $fields);
        echo "[Purestorage] Stored latency metrics\n";
    }
}
