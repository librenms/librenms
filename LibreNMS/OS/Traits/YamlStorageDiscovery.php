<?php
/*
 * YamlDeviceDiscovery.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Models\Storage;
use Illuminate\Support\Collection;
use LibreNMS\Discovery\Yaml\IndexField;
use LibreNMS\Discovery\Yaml\LiteralField;
use LibreNMS\Discovery\Yaml\OidField;
use LibreNMS\Discovery\Yaml\YamlDiscoveryField;
use LibreNMS\Discovery\YamlDiscoveryDefinition;

trait YamlStorageDiscovery
{
    private array $storagePrefetch = [];

    public function discoverYamlStorage(): Collection
    {
        $discovery = YamlDiscoveryDefinition::make(Storage::class)
            ->addField(new LiteralField('poller_type', 'type', $this->getName()))
            ->addField(new YamlDiscoveryField('type', 'storage_type', 'Storage'))
            ->addField(new YamlDiscoveryField('descr', 'storage_descr', 'Disk {{ $index }}'))
            ->addField(new YamlDiscoveryField('units', 'storage_units', 1)) // 1 for percentage only storages
            ->addField(new OidField('size', 'storage_size', should_poll: false))
            ->addField(new OidField('used', 'storage_used'))
            ->addField(new OidField('free', 'storage_free', should_poll: function (YamlDiscoveryDefinition $def) {
                if ($def->getFieldCurrentValue('used') === null || $def->getFieldCurrentValue('size') === null) {
                    return is_numeric($def->getFieldCurrentValue('free'));
                }

                return false;
            }))
            ->addField(new OidField('percent_used', 'storage_perc'))
            ->addField(new YamlDiscoveryField('warn_percent', 'storage_perc_warn', \LibrenmsConfig::get('storage_perc_warn', 80)))
            ->addField(new IndexField('index', 'storage_index', '{{ $index }}'))
            ->afterEach(function (Storage $storage, YamlDiscoveryDefinition $def, $yaml, $index) {
                // fill missing values
                $storage->fillUsage(
                    $storage->storage_used,
                    $storage->storage_size,
                    $storage->storage_free,
                    $storage->storage_perc,
                );
            });

        return $discovery->discover($this->getDiscovery('storage'));
    }
}
