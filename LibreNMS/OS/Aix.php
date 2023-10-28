<?php
/**
 * Aix.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\Storage;
use Illuminate\Support\Collection;
use LibreNMS\OS\Shared\Unix;

class Aix extends Unix
{
    public function discoverOS(Device $device): void
    {
        // don't support server hardware detection or extends
        $this->discoverYamlOS($device);
    }

    public function discoverStorage(): Collection
    {
        return \SnmpQuery::enumStrings()->walk('IBM-AIX-MIB::aixFsTable')->mapTable(function ($data, $index) {
            return (new Storage([
                'type' => 'aix',
                'storage_index' => $index,
                'storage_type' => $data['IBM-AIX-MIB::aixFsType'],
                'storage_descr' => $data['IBM-AIX-MIB::aixFsMountPoint'],
                'storage_used_oid' => ".1.3.6.1.4.1.2.6.191.6.2.1.6.$index",
                'storage_units' => 1024 * 1024,
            ]))->fillUsage(
                total: $data['IBM-AIX-MIB::aixFsSize'] ?? null,
                free: $data['IBM-AIX-MIB::aixFsFree'] ?? null,
            );
        });
    }
}
