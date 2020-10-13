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
        'perc',
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
            $oids = $this->fetchData($yaml);
            $snmp_data = array_merge_recursive($this->mempoolsPrefetch, $this->mempoolsData);

            $count = 1;
            foreach ($this->mempoolsData as $index => $data) {
                foreach ($yaml['skip_values'] ?? [] as $skip_entry) {
                    if (YamlDiscovery::canSkipItem($skip_entry['value'], $index, $skip_entry, [], $snmp_data)) {
                        echo 's';
                        continue 2;
                    }
                }

                $mempools->push((new Mempool([
                    'mempool_index' => YamlDiscovery::replaceValues('index', $index, $count, $yaml, $snmp_data) ?: $index,
                    'mempool_type' => $yaml['type'] ?? $this->getName(),
                    'mempool_precision' => $yaml['precision'] ?? 1,
                    'mempool_descr' => isset($yaml['descr']) ? YamlDiscovery::replaceValues('descr', $index, $count, $yaml, $snmp_data) : 'Memory',
                    'mempool_total_oid' => isset($oids['total']) ? "{$oids['total']}.$index" : null,
                    'mempool_free_oid' => isset($oids['free']) ? "{$oids['free']}.$index" : null,
                    'mempool_used_oid' => isset($oids['used']) ? "{$oids['used']}.$index" : null,
                    'mempool_perc_oid' => isset($oids['perc']) ? "{$oids['perc']}.$index" : null,
                ]))->fillUsage(
                    $data[$yaml['used']] ?? null,
                    $data[$yaml['total']] ?? (is_numeric($yaml['total']) ? $yaml['total'] : null), // allow hard-coded value
                    $data[$yaml['free']] ?? null,
                    $data[$yaml['perc']] ?? null
                ));
                $count++;
            }
        }

        dump($mempools->toArray());

        return $mempools;
    }

    /**
     * @param \Illuminate\Support\Collection $mempools
     */
    public function pollYamlMempools($mempools)
    {
        // fetch all data
        $oids = $mempools->map->only(['mempool_perc_oid', 'mempool_used_oid', 'mempool_free_oid', 'mempool_total_oid'])
            ->flatten()->filter()->unique()->values()->all();
        $data = snmp_get_multi_oid($this->getDeviceArray(), $oids);

        $mempools->each(function (Mempool $mempool) use ($data) {
            $mempool->fillUsage(
                $data[$mempool->mempool_used_oid] ?? null,
                $data[$mempool->mempool_total_oid] ?? null,
                $data[$mempool->mempool_free_oid] ?? null,
                $data[$mempool->mempool_perc_oid] ?? null
            );
        });

        return $mempools;
    }

    /**
     * @param array $yaml item yaml definition
     * @param string $value field from yaml
     * @return array oids for fields
     */
    private function fetchData($yaml)
    {
        $oids = [];
        $this->mempoolsData = []; // clear data from previous mempools

        if (isset($yaml['oid'])) {
            $this->mempoolsData = snmpwalk_cache_oid($this->getDeviceArray(), $yaml['oid'], $this->mempoolsData, null, null, '-OQUb');
        }

        foreach ($this->mempoolsFields as $value) {
            if (isset($yaml[$value]) && ! is_numeric($yaml[$value])) { // allow for hard-coded values
                if (empty($yaml['oid'])) { // if table given, skip individual oids
                    $this->mempoolsData = snmpwalk_cache_oid($this->getDeviceArray(), $yaml[$value], $this->mempoolsData, null, null, '-OQUb');
                }
                $oids[$value] = YamlDiscovery::oidToNumeric($yaml[$value], $this->getDeviceArray());
            }
        }

        return $oids;
    }
}
