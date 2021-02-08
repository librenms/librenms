<?php
/**
 * UcdProcessor.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Models\Mempool;
use LibreNMS\Device\Processor;

trait UcdResources
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        echo 'UCD Resources: ';

        return [
            Processor::discover(
                'ucd-old',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2021.11.11.0',
                0,
                'CPU',
                -1
            ),
        ];
    }

    public function discoverMempools()
    {
        $mempools = collect();
        $data = snmp_get_multi($this->getDeviceArray(), [
            'memTotalSwap.0',
            'memAvailSwap.0',
            'memTotalReal.0',
            'memAvailReal.0',
            'memBuffer.0',
            'memCached.0',
            'memSysAvail.0',
        ], '-OQUs', 'UCD-SNMP-MIB');

        if ($this->oidValid($data, 'memTotalReal') && ($this->oidValid($data, 'memAvailReal') || $this->oidValid($data, 'memSysAvail'))) {
            $mempools->push((new Mempool([
                'mempool_index' => 1,
                'mempool_type' => 'ucd',
                'mempool_class' => 'system',
                'mempool_precision' => 1024,
                'mempool_descr' => 'Physical memory',
                'mempool_free_oid' => '.1.3.6.1.4.1.2021.4.6.0',
            ]))->fillUsage(null, $data[0]['memTotalReal'], $data[0]['memAvailReal'] + $data[0]['memBuffer'] + $data[0]['memCached']));
        }

        if ($this->oidValid($data, 'memTotalSwap') && $this->oidValid($data, 'memAvailSwap')) {
            $mempools->push((new Mempool([
                'mempool_index' => 2,
                'mempool_type' => 'ucd',
                'mempool_class' => 'swap',
                'mempool_precision' => 1024,
                'mempool_descr' => 'Swap space',
                'mempool_free_oid' => '.1.3.6.1.4.1.2021.4.4.0',
            ]))->fillUsage(null, $data[0]['memTotalSwap'], $data[0]['memAvailSwap']));
        }

        if ($this->oidValid($data, 'memBuffer')) {
            $mempools->push((new Mempool([
                'mempool_index' => 3,
                'mempool_type' => 'ucd',
                'mempool_class' => 'buffers',
                'mempool_precision' => 1024,
                'mempool_descr' => 'Memory buffers',
                'mempool_used_oid' => '.1.3.6.1.4.1.2021.4.14.0',
            ]))->fillUsage(null, $data[0]['memTotalReal'], $data[0]['memBuffer']));
        }

        if ($this->oidValid($data, 'memCached')) {
            $mempools->push((new Mempool([
                'mempool_index' => 4,
                'mempool_type' => 'ucd',
                'mempool_class' => 'cached',
                'mempool_precision' => 1024,
                'mempool_descr' => 'Cached memory',
                'mempool_used_oid' => '.1.3.6.1.4.1.2021.4.15.0',
            ]))->fillUsage(null, $data[0]['memTotalReal'], $data[0]['memCached']));
        }

        if ($this->oidValid($data, 'memSysAvail')) {
            $mempools->push((new Mempool([
                'mempool_index' => 5,
                'mempool_type' => 'ucd',
                'mempool_class' => 'available',
                'mempool_precision' => 1024,
                'mempool_descr' => 'Available memory',
                'mempool_free_oid' => '.1.3.6.1.4.1.2021.4.27.0',
            ]))->fillUsage(null, $data[0]['memTotalReal'], $data[0]['memSysAvail']));
        }

        return $mempools;
    }

    private function oidValid($data, $oid)
    {
        return isset($data[0][$oid]) && $data[0][$oid] !== 'NULL';
    }
}
