<?php

/**
 * MefUniEvcMib.php
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Models\MefInfo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use SnmpQuery;

trait MefUniEvcMib
{
    public function discoverMef(): ?Collection
    {
        $evcs = $this->walkMefEvcs();

        if ($evcs === null) {
            return null;
        }

        return collect($evcs)->map(fn (array $entry, int $mefID) => $this->fillMefInfo(new MefInfo(['mefID' => $mefID]), $entry))->values();
    }

    public function pollMef(Collection $mefs): Collection
    {
        $evcs = $this->walkMefEvcs();

        if ($evcs === null) {
            return $mefs;
        }

        foreach ($mefs as $mef) {
            if (! isset($evcs[$mef->mefID])) {
                Log::debug("MEF link {$mef->mefIdent} ({$mef->mefID}) no longer reported, skipping");

                continue;
            }

            $this->fillMefInfo($mef, $evcs[$mef->mefID]);
        }

        return $mefs;
    }

    /**
     * @return array<int, array<string, string>>|null
     */
    private function walkMefEvcs(): ?array
    {
        $response = SnmpQuery::mibs(['MEF-UNI-EVC-MIB'])->cache()->enumStrings()->abortOnFailure()->walk([
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgServiceType',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgIdentifier',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgMtuSize',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgAdminState',
            'MEF-UNI-EVC-MIB::mefServiceEvcCfgRowStatus',
        ]);

        if ($response->getExitCode() !== 0 || ! $response->isValid()) {
            return null;
        }

        return $response->valuesByIndex();
    }

    /**
     * @param  array<string, string>  $entry
     */
    private function fillMefInfo(MefInfo $mef, array $entry): MefInfo
    {
        return $mef->fill([
            'mefType' => $entry['MEF-UNI-EVC-MIB::mefServiceEvcCfgServiceType'],
            'mefIdent' => $entry['MEF-UNI-EVC-MIB::mefServiceEvcCfgIdentifier'],
            'mefMTU' => (int) $entry['MEF-UNI-EVC-MIB::mefServiceEvcCfgMtuSize'],
            'mefAdmState' => $entry['MEF-UNI-EVC-MIB::mefServiceEvcCfgAdminState'],
            'mefRowState' => $entry['MEF-UNI-EVC-MIB::mefServiceEvcCfgRowStatus'],
        ]);
    }
}
