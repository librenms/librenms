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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Models\Storage;
use Illuminate\Support\Collection;
use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Device\YamlDiscoveryField;

trait YamlStorageDiscovery
{
    private array $storagePrefetch = [];

    public function discoverYamlStorage(): Collection
    {
        $storages = YamlDiscovery::from($this->getDiscovery('storage'),
            \App\Models\Storage::class,
            [
                'type' => new YamlDiscoveryField('type', 'storage_type', 'Storage'),
                'descr' => new YamlDiscoveryField('descr', 'storage_descr', 'Disk {{ $index }}'),
                'units' => new YamlDiscoveryField('units', 'storage_units', 1048576), // TODO good default?
                'size' => new YamlDiscoveryField('size', 'storage_size', poll: true),
                'used' => new YamlDiscoveryField('used', 'storage_used', poll: true),
                'free' => new YamlDiscoveryField('free', 'storage_free', poll: true),
                'percent_used' => new YamlDiscoveryField('percent_used', 'storage_perc', poll: true),
                'index' => new YamlDiscoveryField('index', 'storage_index', '{{ $index }}'),
            ], [
                'type' => $this->getName(),
            ], function (Storage $storage, $fields) {
                $storage->fillUsage(
                    $fields['used']->value,
                    $fields['size']->value,
                    $fields['free']->value,
                    $fields['percent_used']->value,
                );
            });

        return $storages;
    }
}
