<?php
/*
 * ArubaosCx.php
 *
 * NAC polling including 802.1x and device-profile entries.
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
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\PortsNac;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Interfaces\Polling\NacPolling;
use SnmpQuery;

class ArubaosCx extends \LibreNMS\OS implements NacPolling, TransceiverDiscovery
{
    protected ?string $entityVendorTypeMib = 'ARUBAWIRED-NETWORKING-OID';

    public function pollNac()
    {
        $nac = new Collection();

        $rowSet = [];
        $table = SnmpQuery::hideMib()->enumStrings()->walk('ARUBAWIRED-PORT-ACCESS-MIB::arubaWiredPortAccessClientTable')->table(2);

        foreach ($table as $ifName => $entry) {
            foreach ($entry as $macKey => $macEntry) {
                $rowSet[$macKey] = [
                    'domain' => '',
                    'ip_address' => '',
                    'host_mode' => '',
                    'authz_by' => '',
                    'username' => '',
                    'timeout' => '',
                ];
                $rowSet[$macKey]['authc_status'] = $macEntry['arubaWiredPacAuthState'] ?? '';
                $rowSet[$macKey]['mac_address'] = $macKey;
                $rowSet[$macKey]['authz_by'] = $macEntry['arubaWiredPacOnboardedMethods'] ?? '';
                $rowSet[$macKey]['authz_status'] = $macEntry['arubaWiredPacAppliedRole'] ?? '';
                $rowSet[$macKey]['username'] = $macEntry['arubaWiredPacUserName'] ?? '';
                $rowSet[$macKey]['vlan'] = $macEntry['arubaWiredPacVlanId'] ?? null;
                $rowSet[$macKey]['port_id'] = (int) PortCache::getIdFromIfName($ifName, $this->getDevice());
                $rowSet[$macKey]['auth_id'] = $ifName;
                $rowSet[$macKey]['method'] = $macEntry['arubaWiredPacOnboardedMethods'] ?? '';
            }
        }

        foreach ($rowSet as $row) {
            $nac->put($row['mac_address'], new PortsNac($row));
        }

        return $nac;
    }

    public function discoverTransceivers(): Collection
    {
        return \SnmpQuery::cache()->walk('ARUBAWIRED-PM-MIB::arubaWiredPmXcvrTable')->mapTable(function ($data, $ifIndex) {
            return new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => $ifIndex,
                'type' => $data['ARUBAWIRED-PM-MIB::arubaWiredPmXcvrDescription'] ?? null,
                'revision' => $data['ARUBAWIRED-PM-MIB::arubaWiredPmXcvrPartNum'] ?? null,
                'model' => $data['ARUBAWIRED-PM-MIB::arubaWiredPmXcvrProductNum'] ?? null,
                'serial' => $data['ARUBAWIRED-PM-MIB::arubaWiredPmXcvrSerialNum'] ?? null,
                'ddm' => ($data['ARUBAWIRED-PM-MIB::arubaWiredPmXcvrDiagnostics'] ?? null) ? 1 : 0,
                'cable' => $data['ARUBAWIRED-PM-MIB::arubaWiredPmXcvrCableType'] ?? null,
                'connector' => $data['ARUBAWIRED-PM-MIB::arubaWiredPmXcvrConnectorType'] ?? null,
                'distance' => $data['ARUBAWIRED-PM-MIB::arubaWiredPmXcvrCableLength'] ?? null,
                'wavelength' => $data['ARUBAWIRED-PM-MIB::arubaWiredPmXcvrWavelength'] ?? null,
                'entity_physical_index' => $ifIndex,
            ]);
        });
    }
}
