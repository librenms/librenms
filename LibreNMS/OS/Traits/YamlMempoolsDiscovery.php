<?php
/*
 * YamlMempoolsDiscovery.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Models\Mempool;
use LibreNMS\Device\YamlDiscovery;

trait YamlMempoolsDiscovery
{
    private $mempoolsData = [];
    private $mempoolsFields = [
        'total',
        'free',
        'used',
        'percent_used',
    ];
    private $mempoolsPrefetch = [];

    public function discoverYamlMempools()
    {
        $mempools = collect();
        $mempools_yaml = $this->getDiscovery('mempools');

        foreach ($mempools_yaml['pre-cache']['oids'] ?? [] as $oid) {
            $this->mempoolsPrefetch = snmpwalk_cache_oid($this->getDeviceArray(), $oid, $this->mempoolsPrefetch, null, null, '-OQUb');
        }

        foreach ($mempools_yaml['data'] as $yaml) {
            $oids = $this->fetchData($yaml, $this->getDiscovery()['mib'] ?? 'ALL');
            $snmp_data = array_merge_recursive($this->mempoolsPrefetch, $this->mempoolsData);

            $count = 1;
            foreach ($this->mempoolsData as $index => $data) {
                if (YamlDiscovery::canSkipItem(null, $index, $yaml, [], $data)) {
                    echo 's';
                    continue;
                }

                $used = $data[$yaml['used']] ?? null;
                $total = $data[$yaml['total']] ?? (is_numeric($yaml['total']) ? $yaml['total'] : null); // allow hard-coded value
                $mempools->push((new Mempool([
                    'mempool_index' => isset($yaml['index']) ? YamlDiscovery::replaceValues('index', $index, $count, $yaml, $snmp_data) : $index,
                    'mempool_type' => $yaml['type'] ?? $this->getName(),
                    'mempool_precision' => $yaml['precision'] ?? 1,
                    'mempool_descr' => isset($yaml['descr']) ? ucwords(YamlDiscovery::replaceValues('descr', $index, $count, $yaml, $snmp_data)) : 'Memory',
                    'mempool_used_oid' => isset($oids['used']) ? YamlDiscovery::oidToNumeric("{$oids['used']}.$index", $this->getDeviceArray()) : null,
                    'mempool_free_oid' => (isset($oids['free']) && ($used === null || $total === null)) ? YamlDiscovery::oidToNumeric("{$oids['free']}.$index", $this->getDeviceArray()) : null, // only use "used" if we have both used and total
                    'mempool_perc_oid' => isset($oids['percent_used']) ? YamlDiscovery::oidToNumeric("{$oids['percent_used']}.$index", $this->getDeviceArray()) : null,
                    'mempool_perc_warn' => $yaml['warn_percent'] ?? 90,
                ]))->fillUsage(
                    $used,
                    $total,
                    $data[$yaml['free']] ?? null,
                    $data[$yaml['percent_used']] ?? null
                ));
                $count++;
            }
        }

//        dump($mempools->toArray());

        return $mempools;
    }

    /**
     * @param array $yaml item yaml definition
     * @return array oids for fields
     * @throws \LibreNMS\Exceptions\InvalidOidException
     */
    private function fetchData($yaml, $mib)
    {
        $oids = [];
        $this->mempoolsData = []; // clear data from previous mempools
        $options = $yaml['snmp_flags'] ?? '-OQUb';

        if (isset($yaml['oid'])) {
            $this->mempoolsData = snmpwalk_cache_oid($this->getDeviceArray(), $yaml['oid'], $this->mempoolsData, null, null, $options);
        }

        foreach ($this->mempoolsFields as $field) {
            if (isset($yaml[$field]) && ! is_numeric($yaml[$field])) { // allow for hard-coded values
                if (empty($yaml['oid'])) { // if table given, skip individual oids
                    $this->mempoolsData = snmpwalk_cache_oid($this->getDeviceArray(), $yaml[$field], $this->mempoolsData, null, null, $options);
                }
                $oids[$field] = YamlDiscovery::oidToNumeric($yaml[$field], $this->getDeviceArray(), $mib);
            }
        }

        return $oids;
    }
}
