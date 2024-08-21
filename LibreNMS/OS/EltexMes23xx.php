<?php
/**
 * EltexMes23xx.php
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
 * @copyright  2022 PipoCanaja
 * @author     PipoCanaja
 */

namespace LibreNMS\OS;

use App\Models\EntPhysical;
use Illuminate\Support\Collection;
use LibreNMS\OS\Shared\Radlan;
use LibreNMS\OS\Traits\EntityMib;
use LibreNMS\Util\StringHelpers;
use SnmpQuery;

class EltexMes23xx extends Radlan
{
    use EntityMib {
        EntityMib::discoverEntityPhysical as discoverBaseEntityPhysical;
    }

    public function discoverEntityPhysical(): Collection
    {
        $inventory = $this->discoverBaseEntityPhysical();

        // add in transceivers
        $trans = SnmpQuery::hideMib()->enumStrings()->walk('ELTEX-MES-PHYSICAL-DESCRIPTION-MIB::eltPhdTransceiverInfoTable')->table(1);
        $ifIndexToEntIndexMap = array_flip($this->getIfIndexEntPhysicalMap());

        foreach ($trans as $ifIndex => $data) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 1000000 + $ifIndex,
                'entPhysicalDescr' => $data['eltPhdTransceiverInfoType'],
                'entPhysicalClass' => 'sfp-cage',
                'entPhysicalName' => strtoupper($data['eltPhdTransceiverInfoConnectorType']),
                'entPhysicalModelName' => $this->normData($data['eltPhdTransceiverInfoPartNumber']),
                'entPhysicalSerialNum' => $data['eltPhdTransceiverInfoSerialNumber'],
                'entPhysicalContainedIn' => $ifIndexToEntIndexMap[$ifIndex] ?? 0,
                'entPhysicalMfgName' => $data['eltPhdTransceiverInfoVendorName'],
                'entPhysicalHardwareRev' => $this->normData($data['eltPhdTransceiverInfoVendorRev']),
                'entPhysicalParentRelPos' => 0,
                'entPhysicalIsFRU' => 'true',
                'ifIndex' => $ifIndex,
            ]));
        }

        return $inventory;
    }

    /**
     * Specific HexToString for Eltex
     */
    protected function normData(string $par = ''): string
    {
        return StringHelpers::isHex($par) ? StringHelpers::hexToAscii($par, ' ') : $par;
    }
}
