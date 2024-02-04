<?php
/*
 * Procurve.php
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\PortsNac;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Polling\NacPolling;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;
use SnmpQuery;

class Procurve extends \LibreNMS\OS implements OSPolling, NacPolling
{
    public function pollOS(DataStorageInterface $datastore): void
    {
        $FdbAddressCount = snmp_get($this->getDeviceArray(), 'hpSwitchFdbAddressCount.0', '-Ovqn', 'STATISTICS-MIB');

        if (is_numeric($FdbAddressCount)) {
            $rrd_def = RrdDefinition::make()->addDataset('value', 'GAUGE', -1, 100000);

            $fields = [
                'value' => $FdbAddressCount,
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'fdb_count', $tags, $fields);

            $this->enableGraph('fdb_count');
        }
    }

    public function pollNac()
    {
        $nac = new Collection();

        $enabled = SnmpQuery::mibs(['IEEE8021-PAE-MIB'])->hideMib()->enumStrings()->get('dot1xPaeSystemAuthControl.0')->value();
        if ($enabled !== 'enabled') {
            return $nac;
        }

        $rowSet = [];
        $ifIndex_map = $this->getDevice()->ports()->pluck('port_id', 'ifIndex');

        $table = SnmpQuery::mibDir('hp')->mibs(['HP-DOT1X-EXTENSIONS-MIB'])->hideMib()->enumStrings()->walk('hpicfDot1xSMAuthConfigTable')->table(2);

        foreach ($table as $ifIndex => $entry) {
            $nacEntry = array_pop($entry);

            $rowSet[$ifIndex] = [
                'domain' => '',
                'ip_address' => '',
                'host_mode' => '',
                'authz_by' => '',
                'username' => '',
            ];

            $rowSet[$ifIndex]['authc_status'] = match ($nacEntry['hpicfDot1xSMAuthPaeState']) {
                null => '',
                'connecting' => 'authcFailed',
                'authenticated' => 'authcSuccess',
                default => $nacEntry['hpicfDot1xSMAuthPaeState']
            };

            $rowSet[$ifIndex]['mac_address'] = $nacEntry['hpicfDot1xSMAuthMacAddr'];

            $rowSet[$ifIndex]['timeout'] = $nacEntry['hpicfDot1xSMAuthSessionTimeout'];
        }

        $table = SnmpQuery::mibs(['IEEE8021-PAE-MIB'])->hideMib()->enumStrings()->walk('dot1xAuthConfigTable')->table(2);
        foreach ($table as $ifIndex => $row) {
            if (! isset($rowSet[$ifIndex])) {
                continue;
            }

            $rowSet[$ifIndex]['auth_id'] = $ifIndex;
            $rowSet[$ifIndex]['authz_status'] = match ($row['dot1xAuthAuthControlledPortStatus']) {
                'authorized' => 'authorizationSuccess',
                'unauthorized' => 'authorizationFailed',
                default => $row['dot1xAuthAuthControlledPortStatus']
            };

            $rowSet[$ifIndex]['port_id'] = $ifIndex_map->get($ifIndex, 0);
        }

        $table = SnmpQuery::mibs(['HP-DOT1X-EXTENSIONS-MIB'])->mibDir('hp')->hideMib()->enumStrings()->walk('hpicfDot1xAuthSessionStatsTable')->table(2);
        foreach ($table as $ifIndex => $entry) {
            if (! isset($rowSet[$ifIndex])) {
                continue;
            }
            $nacEntry = array_pop($entry);

            $rowSet[$ifIndex]['vlan'] = $nacEntry['hpicfDot1xAuthSessionVid'];
            $rowSet[$ifIndex]['authz_by'] = $nacEntry['hpicfDot1xAuthSessionAuthenticMethod'];
            $rowSet[$ifIndex]['username'] = $nacEntry['hpicfDot1xAuthSessionUserName'];
            $rowSet[$ifIndex]['time_elapsed'] = $nacEntry['hpicfDot1xAuthSessionTime'] / 100;
        }

        $table = SnmpQuery::mibs(['HP-DOT1X-EXTENSIONS-MIB'])->hideMib()->enumStrings()->walk('hpicfDot1xPaePortTable')->table(2);
        foreach ($table as $ifIndex => $nacEntry) {
            if (! isset($rowSet[$ifIndex])) {
                continue;
            }

            $rowSet[$ifIndex]['method'] = ($nacEntry['hpicfDot1xPaePortAuth'] === 'true') ? 'dot1x' : '';
        }

        foreach ($rowSet as $row) {
            $nac->put($row['mac_address'], new PortsNac($row));
        }

        return $nac;
    }
}
