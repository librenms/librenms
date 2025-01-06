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
                        $mempool->mempool_used_oid = Oid::of($yaml['used'] . '.' . $index)->toNumeric();
                    }
                    $mempool->mempool_free_oid = null;
                    $mempool->mempool_perc_oid = null;
                } elseif (isset($yaml['free']) && $def->getFieldCurrentValue('free') !== null) {
                    if (isset($yaml['free_num_oid'])) {
                        $mempool->mempool_free_oid = SimpleTemplate::parse($yaml['free_num_oid'], ['index' => $index]);
                    } else {
                        Log::critical('free_num_oid should be added to the discovery yaml to increase performance');
                        $mempool->mempool_free_oid = Oid::of($yaml['free'] . '.' . $index)->toNumeric();
                    }
                    $mempool->mempool_used_oid = null;
                    $mempool->mempool_perc_oid = null;
                } elseif (isset($yaml['percent_used']) && $def->getFieldCurrentValue('percent_used') !== null) {
                    if (isset($yaml['percent_used_num_oid'])) {
                        $mempool->mempool_perc_oid = SimpleTemplate::parse($yaml['percent_used_num_oid'], ['index' => $index]);
                    } else {
                        Log::critical('percent_used_num_oid should be added to the discovery yaml to increase performance');
                        $mempool->mempool_perc_oid = Oid::of($yaml['percent_used'] . '.' . $index)->toNumeric();
                    }
                    $mempool->mempool_used_oid = null;
                    $mempool->mempool_free_oid = null;
                }
            });

        return $def->discover($mempools_yaml);
    }
}
