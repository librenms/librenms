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
use App\View\SimpleTemplate;
use Illuminate\Support\Facades\Log;
use LibreNMS\Discovery\Yaml\IndexField;
use LibreNMS\Discovery\Yaml\OidField;
use LibreNMS\Discovery\Yaml\YamlDiscoveryField;
use LibreNMS\Discovery\YamlDiscoveryDefinition;
use LibreNMS\Util\Oid;

trait YamlMempoolsDiscovery
{

    public function discoverYamlMempools()
    {
        $mempools_yaml = $this->getDiscovery('mempools');

        $def = YamlDiscoveryDefinition::make(Mempool::class)
            ->addField(new IndexField('index', 'mempool_index'))
            ->addField(new YamlDiscoveryField('type', 'mempool_type', $this->getName()))
            ->addField(new YamlDiscoveryField('class', 'mempool_class', 'system'))
            ->addField(new YamlDiscoveryField('precision', 'mempool_precision', 1))
            ->addField(new YamlDiscoveryField('descr', 'mempool_descr', 'Memory', callback: fn($value) => ucwords($value)))
            ->addField(new OidField('used','mempool_used'))
            ->addField(new OidField('free','mempool_free'))
            ->addField(new OidField('total','mempool_total'))
            ->addField(new OidField('percent_used','mempool_prec'))
            ->addField(new YamlDiscoveryField('warn_percent', 'mempool_perc_warn', 90))
            ->afterEach(function (Mempool $mempool, YamlDiscoveryDefinition $def, $yaml, $index) {
                // fill numeric oid that should be polled
                if (isset($yaml['used']) && $def->getFieldCurrentValue('used') !== null) {
                    if (isset($yaml['used_num_oid'])) {
                        $mempool->mempool_used_oid = SimpleTemplate::parse($yaml['used_num_oid'], ['index' => $index]);
                    } else {
                        Log::critical('used_num_oid should be added to the discovery yaml to increase performance');
                        $mempool->mempool_used_oid = Oid::toNumeric($yaml['used'] . '.' . $index);
                    }
                    $mempool->mempool_free_oid = null;
                    $mempool->mempool_perc_oid = null;
                } elseif (isset($yaml['free']) && $def->getFieldCurrentValue('free') !== null) {
                    if (isset($yaml['free_num_oid'])) {
                        $mempool->mempool_free_oid = SimpleTemplate::parse($yaml['free_num_oid'], ['index' => $index]);
                    } else {
                        Log::critical('free_num_oid should be added to the discovery yaml to increase performance');
                        $mempool->mempool_free_oid = Oid::toNumeric($yaml['free'] . '.' . $index);
                    }
                    $mempool->mempool_used_oid = null;
                    $mempool->mempool_perc_oid = null;
                } elseif (isset($yaml['percent_used']) && $def->getFieldCurrentValue('percent_used') !== null) {
                    if (isset($yaml['percent_used_num_oid'])) {
                        $mempool->mempool_perc_oid = SimpleTemplate::parse($yaml['percent_used_num_oid'], ['index' => $index]);
                    } else {
                        Log::critical('percent_used_num_oid should be added to the discovery yaml to increase performance');
                        $mempool->mempool_perc_oid = Oid::toNumeric($yaml['percent_used'] . '.' . $index);
                    }
                    $mempool->mempool_used_oid = null;
                    $mempool->mempool_free_oid = null;
                }

                $used = $this->getData('used', $index, $yaml);
                $total = $this->getData('total', $index, $yaml);
                $mempool = (new Mempool([
                    'mempool_index' => isset($yaml['index']) ? YamlDiscovery::replaceValues('index', $index, $count, $yaml, $snmp_data) : $index,
                    'mempool_type' => $yaml['type'] ?? $this->getName(),
                    'mempool_class' => $yaml['class'] ?? 'system',
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

                $fields = $def->getFields();
                $mempool->fillUsage(
                    $fields['used']->value,
                    $fields['total']->value,
                    $fields['free']->value,
                    $fields['percent_used']->value,
                );
            });

        return $def->discover($mempools_yaml);
    }

    private function getData($field, $index, $yaml)
    {
        $data = $yaml[$field] ?? null;
        if (isset($this->mempoolsData[$index][$data])) {
            return $this->mempoolsData[$index][$data];
        }

        if (isset($this->mempoolsPrefetch[$index][$data])) {
            return $this->mempoolsPrefetch[$index][$data];
        }

        return is_numeric($data) ? $data : null;  // hard coded number
    }

    private function getOid($field, $index, $yaml)
    {
        if (Oid::of($yaml[$field] ?? '')->isNumeric()) {
            return $yaml[$field];
        }

        if (isset($this->mempoolsOids[$field])) {
            return Oid::of("{$this->mempoolsOids[$field]}.$index")->toNumeric();
        }

        return null;
    }
}
