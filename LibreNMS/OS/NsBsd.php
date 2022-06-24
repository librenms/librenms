<?php
/**
 * NsBsd.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\Mempool;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Polling\MempoolsPolling;
use LibreNMS\OS;

class NsBsd extends \LibreNMS\OS implements MempoolsDiscovery, MempoolsPolling
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $device->sysName = \SnmpQuery::get('STORMSHIELD-PROPERTY-MIB::snsSystemName.0')->value() ?: $device->sysName;
    }
    public function discoverMempools()
    {
        $data = snmp_get_multi_oid($this->getDeviceArray(), ['STORMSHIELD-SYSTEM-MONITOR-MIB::snsMem.0'], '-OUQs', 'STORMSHIELD-SYSTEM-MONITOR-MIB');

        if (empty($data)) {
            return collect();
        }

        $mempool = new Mempool([
            'mempool_index' => 0,
            'mempool_type' => 'NsBsd',
            'mempool_class' => 'system',
            'mempool_precision' => 1,
            'mempool_descr' => 'Memory',
            'mempool_perc_warn' => 80,
        ]);

        if (!$data['snsMem.0']) {
                return collect();
        }

        $snsMem_Array = explode(',', $data['snsMem.0']);
        $mempools = collect();
        if (count($snsMem_Array) == 6) {
                $mempools->push(
                        (new Mempool([
                                        'mempool_index' => '0',
                                            'mempool_type' => 'NsBsd',
                                            'mempool_class' => 'system',
                                            'mempool_precision' => 1,
                                            'mempool_descr' => 'Host',
                                            'mempool_perc_warn' => 80,
                                ]))->fillUsage(null,null,null,intval($snsMem_Array[0]))
                );
                $mempools->push(
                        (new Mempool([
                                        'mempool_index' => '1',
                                            'mempool_type' => 'NsBsd',
                                            'mempool_class' => 'system',
                                            'mempool_precision' => 1,
                                            'mempool_descr' => 'Fragments',
                                            'mempool_perc_warn' => 80,
                                ]))->fillUsage(null,null,null,intval($snsMem_Array[1]))
                );
                $mempools->push(
                        (new Mempool([
                                        'mempool_index' => '2',
                                            'mempool_type' => 'NsBsd',
                                            'mempool_class' => 'system',
                                            'mempool_precision' => 1,
                                            'mempool_descr' => 'ICMP',
                                            'mempool_perc_warn' => 80,
                                ]))->fillUsage(null,null,null,intval($snsMem_Array[2]))
                );
                $mempools->push(
                        (new Mempool([
                                        'mempool_index' => '3',
                                            'mempool_type' => 'NsBsd',
                                            'mempool_class' => 'system',
                                            'mempool_precision' => 1,
                                            'mempool_descr' => 'Connections',
                                            'mempool_perc_warn' => 80,
                                ]))->fillUsage(null,null,null,intval($snsMem_Array[3]))
                );
                $mempools->push(
                        (new Mempool([
                                        'mempool_index' => '4',
                                            'mempool_type' => 'NsBsd',
                                            'mempool_class' => 'system',
                                            'mempool_precision' => 1,
                                            'mempool_descr' => 'Data tracking',
                                            'mempool_perc_warn' => 80,
                                ]))->fillUsage(null,null,null,intval($snsMem_Array[4]))
                );
                $mempools->push(
                        (new Mempool([
                                        'mempool_index' => '5',
                                            'mempool_type' => 'NsBsd',
                                            'mempool_class' => 'system',
                                            'mempool_precision' => 1,
                                            'mempool_descr' => 'Dynamic',
                                            'mempool_perc_warn' => 80,
                                ]))->fillUsage(null,null,null,intval($snsMem_Array[5]))
                );
        } elseif (count($snsMem_Array) == 7) {
                $mempools->push(
                        (new Mempool([
                                        'mempool_index' => '0',
                                            'mempool_type' => 'NsBsd',
                                            'mempool_class' => 'system',
                                            'mempool_precision' => 1,
                                            'mempool_descr' => 'Host',
                                            'mempool_perc_warn' => 80,
                                ]))->fillUsage(null,null,null,intval($snsMem_Array[0]))
                );
                $mempools->push(
                        (new Mempool([
                                        'mempool_index' => '1',
                                            'mempool_type' => 'NsBsd',
                                            'mempool_class' => 'system',
                                            'mempool_precision' => 1,
                                            'mempool_descr' => 'Fragments',
                                            'mempool_perc_warn' => 80,
                                ]))->fillUsage(null,null,null,intval($snsMem_Array[1]))
                );
                $mempools->push(
                        (new Mempool([
                                        'mempool_index' => '2',
                                            'mempool_type' => 'NsBsd',
                                            'mempool_class' => 'system',
                                            'mempool_precision' => 1,
                                            'mempool_descr' => 'ICMP',
                                            'mempool_perc_warn' => 80,
                                ]))->fillUsage(null,null,null,intval($snsMem_Array[2]))
                );
                $mempools->push(
                        (new Mempool([
                                        'mempool_index' => '3',
                                            'mempool_type' => 'NsBsd',
                                            'mempool_class' => 'system',
                                            'mempool_precision' => 1,
                                            'mempool_descr' => 'Connections',
                                            'mempool_perc_warn' => 80,
                                ]))->fillUsage(null,null,null,intval($snsMem_Array[3]))
                );
                $mempools->push(
                        (new Mempool([
                                        'mempool_index' => '4',
                                            'mempool_type' => 'NsBsd',
                                            'mempool_class' => 'system',
                                            'mempool_precision' => 1,
                                            'mempool_descr' => 'Ether state',
                                            'mempool_perc_warn' => 80,
                                ]))->fillUsage(null,null,null,intval($snsMem_Array[4]))
                );
                $mempools->push(
                        (new Mempool([
                                        'mempool_index' => '5',
                                            'mempool_type' => 'NsBsd',
                                            'mempool_class' => 'system',
                                            'mempool_precision' => 1,
                                            'mempool_descr' => 'Data tracking',
                                            'mempool_perc_warn' => 80,
                                ]))->fillUsage(null,null,null,intval($snsMem_Array[5]))
                );
                $mempools->push(
                        (new Mempool([
                                        'mempool_index' => '6',
                                            'mempool_type' => 'NsBsd',
                                            'mempool_class' => 'system',
                                            'mempool_precision' => 1,
                                            'mempool_descr' => 'Dynamic',
                                            'mempool_perc_warn' => 80,
                                ]))->fillUsage(null,null,null,intval($snsMem_Array[6]))
                );
        }
        return $mempools;
    }

    public function pollMempools($mempools)
    {
        $data = snmp_get_multi_oid($this->getDeviceArray(), ['STORMSHIELD-SYSTEM-MONITOR-MIB::snsMem.0'], '-OUQs', 'STORMSHIELD-SYSTEM-MONITOR-MIB');

        if (empty($data)) {
            return $mempools;
        }


        if (!$data['snsMem.0']) {
                return $mempools;
        }

        $snsMem_Array = explode(',', $data['snsMem.0']);
        $c = $mempools->count();

        if ($c == 6) {
                $mapping = [
                        'Host' => 0,
                        'Fragments' => 1,
                        'ICMP' => 2,
                        'Connections' => 3,
                        'Data tracking' => 4,
                        'Dynamic' => 5,
                ];
                foreach($mempools as $m) {
                        $index = $mapping[$m->mempool_descr];
                        $m->fillUsage(null,null,null,intval($snsMem_Array[$index]));
                }
        } elseif ($c == 7) {
                $mapping = [
                        'Host' => 0,
                        'Fragments' => 1,
                        'ICMP' => 2,
                        'Connections' => 3,
                        'Ether state' => 4,
                        'Data tracking' => 5,
                        'Dynamic' => 6,
                ];
                foreach($mempools as $m) {
                        $index = $mapping[$m->mempool_descr];
                        $m->fillUsage(null,null,null,intval($snsMem_Array[$index]));
                }
        }
        return $mempools;
    }

}
