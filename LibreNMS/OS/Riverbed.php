<?php
/**
 * Riverbed.php
 *
 * -Description-
 *
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * Copyright (c) 2017 Cercel Valentin <crc@nuamchefazi.ro>
 */

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Riverbed extends OS implements OSPolling
{
    public function pollOS(DataStorageInterface $datastore): void
    {
        /* optimisation oids
         *
         * half-open   .1.3.6.1.4.1.17163.1.1.5.2.3.0
         * half-closed .1.3.6.1.4.1.17163.1.1.5.2.4.0
         * establised  .1.3.6.1.4.1.17163.1.1.5.2.5.0
         * active      .1.3.6.1.4.1.17163.1.1.5.2.6.0
         * total       .1.3.6.1.4.1.17163.1.1.5.2.7.0
         *
         */

        $conn_array = [
            '.1.3.6.1.4.1.17163.1.1.5.2.3.0',
            '.1.3.6.1.4.1.17163.1.1.5.2.4.0',
            '.1.3.6.1.4.1.17163.1.1.5.2.5.0',
            '.1.3.6.1.4.1.17163.1.1.5.2.6.0',
            '.1.3.6.1.4.1.17163.1.1.5.2.7.0',
        ];
        $connections = snmp_get_multi_oid($this->getDeviceArray(), $conn_array);

        $conn_half_open = $connections['.1.3.6.1.4.1.17163.1.1.5.2.3.0'] ?? null;
        $conn_half_closed = $connections['.1.3.6.1.4.1.17163.1.1.5.2.4.0'] ?? null;
        $conn_established = $connections['.1.3.6.1.4.1.17163.1.1.5.2.5.0'] ?? null;
        $conn_active = $connections['.1.3.6.1.4.1.17163.1.1.5.2.6.0'] ?? null;
        $conn_total = $connections['.1.3.6.1.4.1.17163.1.1.5.2.7.0'] ?? null;

        if ($conn_half_open >= 0 && $conn_half_closed >= 0 && $conn_established >= 0 && $conn_active >= 0 && $conn_total >= 0) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('half_open', 'GAUGE', 0)
                ->addDataset('half_closed', 'GAUGE', 0)
                ->addDataset('established', 'GAUGE', 0)
                ->addDataset('active', 'GAUGE', 0)
                ->addDataset('total', 'GAUGE', 0);

            $fields = [
                'half_open' => $conn_half_open,
                'half_closed' => $conn_half_closed,
                'established' => $conn_established,
                'active' => $conn_active,
                'total' => $conn_total,
            ];

            $tags = compact('rrd_def');

            $datastore->put($this->getDeviceArray(), 'riverbed_connections', $tags, $fields);
            $this->enableGraph('riverbed_connections');
        }

        /* datastore oids
         *
         * hits .1.3.6.1.4.1.17163.1.1.5.4.1.0
         * miss .1.3.6.1.4.1.17163.1.1.5.4.2.0
         *
         */
        $datastore_array = [
            '.1.3.6.1.4.1.17163.1.1.5.4.1.0',
            '.1.3.6.1.4.1.17163.1.1.5.4.2.0',
        ];
        $ds_data = snmp_get_multi_oid($this->getDeviceArray(), $datastore_array);

        $datastore_hits = $ds_data['.1.3.6.1.4.1.17163.1.1.5.4.1.0'] ?? null;
        $datastore_miss = $ds_data['.1.3.6.1.4.1.17163.1.1.5.4.2.0'] ?? null;

        if ($datastore_hits >= 0 && $datastore_miss >= 0) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('datastore_hits', 'GAUGE', 0)
                ->addDataset('datastore_miss', 'GAUGE', 0);

            $fields = [
                'datastore_hits' => $datastore_hits,
                'datastore_miss' => $datastore_miss,
            ];

            $tags = compact('rrd_def');

            $datastore->put($this->getDeviceArray(), 'riverbed_datastore', $tags, $fields);
            $this->enableGraph('riverbed_datastore');
        }

        /* optimization oids
         *
         * optimized   .1.3.6.1.4.1.17163.1.1.5.2.1.0
         * passthrough .1.3.6.1.4.1.17163.1.1.5.2.2.0
         *
         */
        $optimization_array = [
            '.1.3.6.1.4.1.17163.1.1.5.2.1.0',
            '.1.3.6.1.4.1.17163.1.1.5.2.2.0',
        ];

        $optimizations = snmp_get_multi_oid($this->getDeviceArray(), $optimization_array);

        $conn_optimized = $optimizations['.1.3.6.1.4.1.17163.1.1.5.2.1.0'] ?? null;
        $conn_passthrough = $optimizations['.1.3.6.1.4.1.17163.1.1.5.2.2.0'] ?? null;

        if ($conn_optimized >= 0 && $conn_passthrough >= 0) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('conn_optimized', 'GAUGE', 0)
                ->addDataset('conn_passthrough', 'GAUGE', 0);

            $fields = [
                'conn_optimized' => $conn_optimized,
                'conn_passthrough' => $conn_passthrough,
            ];

            $tags = compact('rrd_def');

            $datastore->put($this->getDeviceArray(), 'riverbed_optimization', $tags, $fields);
            $this->enableGraph('riverbed_optimization');
        }

        /* bandwidth passthrough
         *
         * in .1.3.6.1.4.1.17163.1.1.5.3.3.1.0
         * out .1.3.6.1.4.1.17163.1.1.5.3.3.2.0
         * total .1.3.6.1.4.1.17163.1.1.5.3.3.3.0
         *
         */

        $bandwidth_array = [
            '.1.3.6.1.4.1.17163.1.1.5.3.3.1.0',
            '.1.3.6.1.4.1.17163.1.1.5.3.3.2.0',
            '.1.3.6.1.4.1.17163.1.1.5.3.3.3.0',
        ];

        $bandwidth = snmp_get_multi_oid($this->getDeviceArray(), $bandwidth_array);

        $bw_in = $bandwidth['.1.3.6.1.4.1.17163.1.1.5.3.3.1.0'] ?? null;
        $bw_out = $bandwidth['.1.3.6.1.4.1.17163.1.1.5.3.3.2.0'] ?? null;
        $bw_total = $bandwidth['.1.3.6.1.4.1.17163.1.1.5.3.3.3.0'] ?? null;

        if ($bw_in >= 0 && $bw_out >= 0 && $bw_total >= 0) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('bw_in', 'COUNTER', 0)
                ->addDataset('bw_out', 'COUNTER', 0)
                ->addDataset('bw_total', 'COUNTER', 0);

            $fields = [
                'bw_in' => $bw_in,
                'bw_out' => $bw_out,
                'bw_total' => $bw_total,
            ];

            $tags = compact('rrd_def');

            $datastore->put($this->getDeviceArray(), 'riverbed_passthrough', $tags, $fields);
            $this->enableGraph('riverbed_passthrough');
        }
    }
}
