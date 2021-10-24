<?php
/**
 * Storage.php
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

namespace LibreNMS\Modules;

use App\Observers\ModuleModelObserver;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Discovery\StorageDiscovery;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Storage implements \LibreNMS\Interfaces\Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        if ($os instanceof StorageDiscovery) {
            $data = $os->discoverStorage()->filter->isValid($os->getName());

            dd($data->toArray());

            ModuleModelObserver::observe(\App\Models\Storage::class);
            $this->syncModels($os->getDevice(), 'storage', $data);
        }
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os)
    {
        foreach (dbFetchRows('SELECT * FROM storage WHERE device_id = ?', [$device['device_id']]) as $storage) {
            $descr = $storage['storage_descr'];
            $mib = $storage['storage_mib'];

            echo 'Storage ' . $descr . ': ' . $mib . "\n\n\n\n";

            $rrd_name = ['storage', $mib, $descr];
            $rrd_def = RrdDefinition::make()
                ->addDataset('used', 'GAUGE', 0)
                ->addDataset('free', 'GAUGE', 0);

            $file = \LibreNMS\Config::get('install_dir') . '/includes/polling/storage/' . $mib . '.inc.php';
            if (is_file($file)) {
                include $file;
            }

            d_echo($storage);

            if ($storage['size']) {
                $percent = round(($storage['used'] / $storage['size'] * 100));
            } else {
                $percent = 0;
            }

            echo $percent . '% ';

            $fields = [
                'used'   => $storage['used'],
                'free'   => $storage['free'],
            ];

            $tags = compact('mib', 'descr', 'rrd_name', 'rrd_def');
            data_update($device, 'storage', $tags, $fields);

            // NOTE: casting to string for mysqli bug (fixed by mysqlnd)
            $update = dbUpdate(['storage_used' => (string) $storage['used'], 'storage_free' => (string) $storage['free'], 'storage_size' => (string) $storage['size'], 'storage_units' => $storage['units'], 'storage_perc' => $percent], 'storage', '`storage_id` = ?', [$storage['storage_id']]);

            echo "\n";
        }//end foreach

        unset($storage);
    }

    /**
     * @inheritDoc
     */
    public function cleanup(OS $os)
    {
        // TODO: Implement cleanup() method.
    }
}
