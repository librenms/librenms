<?php

/**
 * DellSonic.php
 *
 * Dell SONiC OS
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
 */

namespace LibreNMS\OS;

use App\Models\EntPhysical;
use Illuminate\Support\Collection;
use LibreNMS\OS\Traits\EntityMib;
use LibreNMS\OS;
use SnmpQuery;

class DellSonic extends OS
{
    use EntityMib {EntityMib::discoverEntityPhysical as discoverBaseEntityPhysical; }

    /**
     * @return Collection<int, entPhysical>
     */
    public function discoverEntityPhysical(): Collection
    {
        // normal ENTITY-MIB collection
        $inventory = $this->discoverBaseEntityPhysical();

        // add additional data from Dell MIBs (either MIB pair works)
        // $extra = SnmpQuery::walk('DELL-NETWORKING-CHASSIS-MIB::dellNetStackUnitTable')->table(1);
        $extra = SnmpQuery::walk('Dell-Vendor-MIB::productIdentificationPerUnitTable')->table(1);

        $inventory->each(function (EntPhysical $entry) use ($extra): void {
            if (isset($entry->entPhysicalIndex)) {
                if ($entry['entPhysicalClass'] == 'chassis') {
                    // $entry->entPhysicalAssetID = $extra[$entry->entPhysicalParentRelPos - 1]['DELL-NETWORKING-CHASSIS-MIB::dellNetStackUnitServiceTag'];
                    $entry->entPhysicalAssetID = $extra[$entry->entPhysicalParentRelPos - 1]['Dell-Vendor-MIB::productIdentificationServiceTag'];
                }
            }
        });

        return $inventory;
    }
}
