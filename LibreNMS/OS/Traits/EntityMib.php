<?php

/**
 * EntityMib.php
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
 * @copyright  2024 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Models\EntPhysical;
use Illuminate\Support\Collection;

trait EntityMib
{
    public function discoverEntityPhysical(): Collection
    {
        $snmpQuery = \SnmpQuery::hideMib()->enumStrings();
        if (isset($this->entityVendorTypeMib)) {
            $snmpQuery = $snmpQuery->mibs([$this->entityVendorTypeMib]);
        }
        $data = $snmpQuery->walk('ENTITY-MIB::entPhysicalTable');

        if (! $data->isValid()) {
            return new Collection;
        }

        $entPhysicalToIfIndexMap = $this->getIfIndexEntPhysicalMap();

        return $data->mapTable(function ($data, $entityPhysicalIndex) use ($entPhysicalToIfIndexMap) {
            $entityPhysical = new EntPhysical($data);
            $entityPhysical->entPhysicalIndex = $entityPhysicalIndex;
            // get ifIndex. also if parent has an ifIndex, set it too
            $entityPhysical->ifIndex = $entPhysicalToIfIndexMap[$entityPhysicalIndex] ?? $entPhysicalToIfIndexMap[(int) $entityPhysical->entPhysicalContainedIn] ?? null;

            return $entityPhysical;
        });
    }

    /**
     * @return array<int, int>
     */
    protected function getIfIndexEntPhysicalMap(): array
    {
        $mapping = \SnmpQuery::cache()->walk('ENTITY-MIB::entAliasMappingIdentifier')->table(2);
        $map = [];

        foreach ($mapping as $entityPhysicalIndex => $data) {
            $id = $data[0]['ENTITY-MIB::entAliasMappingIdentifier'] ?? $data[1]['ENTITY-MIB::entAliasMappingIdentifier'] ?? null;
            if ($id && preg_match('/ifIndex[\[.](\d+)/', (string) $id, $matches)) {
                $map[(int) $entityPhysicalIndex] = (int) $matches[1];
            }
        }

        return $map;
    }
}
