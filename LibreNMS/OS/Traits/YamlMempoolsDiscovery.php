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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Models\Mempool;
use LibreNMS\Device\YamlDiscovery;

trait YamlMempoolsDiscovery
{
    private $mempoolsData = [];
    private $mempoolsOids = [];
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
            $options = $mempools_yaml['pre-cache']['snmp_flags'] ?? '-OQUb';
            $this->mempoolsPrefetch = snmpwalk_cache_oid($this->getDeviceArray(), $oid, $this->mempoolsPrefetch, null, null, $options);
        }

        foreach ($mempools_yaml['data'] as $yaml) {
            $this->fetchData($yaml, $this->getDiscovery()['mib'] ?? 'ALL');
            $snmp_data = array_replace_recursive($this->mempoolsPrefetch, $this->mempoolsData);

            $count = 1;
            foreach ($this->mempoolsData as $index => $data) {
                if (YamlDiscovery::canSkipItem(null, $index, $yaml, [], $data)) {
                    echo 's';
                    continue;
                }

                $used = $this->getData('used', $index, $yaml);
                $total = $this->getData('total', $index, $yaml);
                $mempool = (new Mempool([
                    'mempool_index' => isset($yaml['index']) ? YamlDiscovery::replaceValues('index', $index, $count, $yaml, $snmp_data) : $index,
                    'mempool_type' => $yaml['type'] ?? $this->getName(),
                    'mempool_class' =>$yaml['class'] ?? 'system',
                    'mempool_precision' => $yaml['precision'] ?? 1,
                    'mempool_descr' => isset($yaml['descr']) ? ucwords(YamlDiscovery::replaceValues('descr', $index, $count, $yaml, $snmp_data)) : 'Memory',
                    'mempool_used_oid' => $this->getOid('used', $index, $yaml),
                    'mempool_free_oid' => ($used === null || $total === null) ? $this->getOid('free', $index, $yaml) : null, // only use "free" if we have both used and total
                    'mempool_perc_oid' => $this->getOid('percent_used', $index, $yaml),
                    'mempool_perc_warn' => isset($yaml['warn_percent']) ? YamlDiscovery::replaceValues('warn_percent', $index, $count, $yaml, $snmp_data) : 90,
                ]))->fillUsage(
                    $used,
                    $total,
                    $this->getData('free', $index, $yaml),
                    $this->getData('percent_used', $index, $yaml)
                );

                if ($mempool->mempool_total) {
                    $mempools->push($mempool);
                    $count++;
                }
            }
        }

        return $mempools;
    }

    private function getData($field, $index, $yaml)
    {
        $oid = $yaml[$field];
        if (isset($this->mempoolsData[$index][$oid])) {
            return $this->mempoolsData[$index][$oid];
        }

        if (isset($this->mempoolsPrefetch[$index][$oid])) {
            return $this->mempoolsPrefetch[$index][$oid];
        }

        return is_numeric($yaml[$field]) ? $yaml[$field] : null;  // hard coded number
    }

    private function getOid($field, $index, $yaml)
    {
        if (YamlDiscovery::oidIsNumeric($yaml[$field])) {
            return $yaml[$field];
        }

        if (isset($this->mempoolsOids[$field])) {
            return YamlDiscovery::oidToNumeric("{$this->mempoolsOids[$field]}.$index", $this->getDeviceArray());
        }

        return null;
    }

    /**
     * @param array $yaml item yaml definition
     * @param string $mib
     * @throws \LibreNMS\Exceptions\InvalidOidException
     */
    private function fetchData($yaml, $mib)
    {
        $this->mempoolsOids = [];
        $this->mempoolsData = []; // clear data from previous mempools
        $options = $yaml['snmp_flags'] ?? '-OQUb';

        if (isset($yaml['oid'])) {
            $this->mempoolsData = snmpwalk_cache_oid($this->getDeviceArray(), $yaml['oid'], $this->mempoolsData, null, null, $options);
        }

        foreach ($this->mempoolsFields as $field) {
            $oid = $yaml[$field];
            if (isset($oid) && ! is_numeric($oid)) { // allow for hard-coded values
                if (YamlDiscovery::oidIsNumeric($oid)) { // if numeric oid, it is not a table, just fetch it
                    $this->mempoolsData[0][$oid] = snmp_get($this->getDeviceArray(), $oid, '-Oqv');
                    continue;
                }

                if (empty($yaml['oid'])) { // if table given, skip individual oids
                    $this->mempoolsData = snmpwalk_cache_oid($this->getDeviceArray(), $oid, $this->mempoolsData, null, null, $options);
                }
                $this->mempoolsOids[$field] = YamlDiscovery::oidToNumeric($oid, $this->getDeviceArray(), $mib);
            }
        }
    }
}
