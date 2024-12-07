<?php
/**
 * Edgeos.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\EntPhysical;
use LibreNMS\OS\Traits\EntityMib;
use LibreNMS\Util\StringHelpers;

class Edgeos extends \LibreNMS\OS
{
    use EntityMib {
        EntityMib::discoverEntityPhysical as discoverBaseEntityPhysical;
    }

    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $hw = snmpwalk_cache_oid($this->getDeviceArray(), 'hrSWRunParameters', [], 'HOST-RESOURCES-MIB');
        foreach ($hw as $entry) {
            if (preg_match('/(?<=UBNT )(.*)(?= running on)/', $entry['hrSWRunParameters'], $matches)) {
                $this->getDevice()->hardware = $matches[0];
                break;
            }
        }
    }

    public function discoverEntityPhysical(): \Illuminate\Support\Collection
    {
        return $this->discoverBaseEntityPhysical()->each(function (EntPhysical $entity) {
            // clean garbage in fields "...............\n00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00"
            $entity->entPhysicalDescr = StringHelpers::trimHexGarbage($entity->entPhysicalDescr);
            $entity->entPhysicalName = StringHelpers::trimHexGarbage($entity->entPhysicalName);
            $entity->entPhysicalVendorType = StringHelpers::trimHexGarbage($entity->entPhysicalVendorType);
        });
    }
}
