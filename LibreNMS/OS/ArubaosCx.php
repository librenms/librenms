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

use App\Models\PortsNac;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Polling\NacPolling;
use SnmpQuery;

class ArubaosCx extends \LibreNMS\OS implements NacPolling
{
    protected ?string $entityVendorTypeMib = 'ARUBAWIRED-NETWORKING-OID';

    public function pollNac()
    {
        $nac = new Collection();

        $rowSet = [];
        $ifIndex_map = $this->getDevice()->ports()->pluck('port_id', 'ifName');
        $table = SnmpQuery::hideMib()->enumStrings()->walk('ARUBAWIRED-PORT-ACCESS-MIB::arubaWiredPortAccessClientTable')->table(2);

        foreach ($table as $ifIndex => $entry) {
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
                $rowSet[$macKey]['authz_status'] = '';
                $rowSet[$macKey]['username'] = $macEntry['arubaWiredPacUserName'] ?? '';
                $rowSet[$macKey]['vlan'] = $macEntry['arubaWiredPacVlanId'] ?? null;
                $rowSet[$macKey]['port_id'] = $ifIndex_map->get($ifIndex, 0);
                $rowSet[$macKey]['auth_id'] = $ifIndex;
                $rowSet[$macKey]['method'] = $macEntry['arubaWiredPacOnboardedMethods'] ?? '';
            }
        }

        foreach ($rowSet as $row) {
            $nac->put($row['mac_address'], new PortsNac($row));
        }

        return $nac;
    }
}
