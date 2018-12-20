<?php
/**
 * nac.inc.php
 *
 * Cisco network access controls poller module
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Jose Augusto Cardoso
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use App\Models\Port;
use App\Models\PortsNac;
use LibreNMS\Util\DiscoveryModelObserver;
use LibreNMS\Util\IP;

echo "\nCisco-NAC\n";

// cache port ifIndex -> port_id map
$ports_map = Port::where('device_id', $device['device_id'])->pluck('port_id', 'ifIndex');
$port_nac_ids = [];

// discovery output (but don't install it twice (testing can can do this)
if (!PortsNac::getEventDispatcher()->hasListeners('eloquent.created: App\Models\PortsNac')) {
    PortsNac::observe(new DiscoveryModelObserver());
}

// collect data via snmp and reorganize the session method entry a bit
$portAuthSessionEntry = snmpwalk_cache_oid($device, 'cafSessionEntry', [], 'CISCO-AUTH-FRAMEWORK-MIB');
if (!empty($portAuthSessionEntry)) {
    $cafSessionMethodsInfoEntry = collect(snmpwalk_cache_oid($device, 'cafSessionMethodsInfoEntry', [], 'CISCO-AUTH-FRAMEWORK-MIB'))->mapWithKeys(function ($item, $key) {
        $key_parts = explode('.', $key);
        $key = implode('.', array_slice($key_parts, 0, 2)); // remove the auth method
        return [$key => ['method' => $key_parts[2], 'authc_status' => $item['cafSessionMethodState']]];
    });
}

// update the DB
foreach ($portAuthSessionEntry as $index => $portAuthSessionEntryParameters) {
    $auth_id = trim(strstr($index, "'"), "'");
    $ifIndex = substr($index, 0, strpos($index, "."));
    $session_info = $cafSessionMethodsInfoEntry->get($ifIndex . '.' . $auth_id);

    $port_nac = PortsNac::updateOrCreate([
        'port_id' => $ports_map->get($ifIndex, 0),
        'mac_address' => strtolower(implode(array_map('zeropad', explode(':', $portAuthSessionEntryParameters['cafSessionClientMacAddress'])))),
    ], [
        'auth_id' => $auth_id,
        'device_id' => $device['device_id'],
        'domain' => $portAuthSessionEntryParameters['cafSessionDomain'],
        'username' => $portAuthSessionEntryParameters['cafSessionAuthUserName'],
        'ip_address' => (string)IP::fromHexString($portAuthSessionEntryParameters['cafSessionClientAddress'], true),
        'host_mode' => $portAuthSessionEntryParameters['cafSessionAuthHostMode'],
        'authz_status' => $portAuthSessionEntryParameters['cafSessionStatus'],
        'authz_by' => $portAuthSessionEntryParameters['cafSessionAuthorizedBy'],
        'authc_status' => $session_info['authc_status'],
        'timeout' => $portAuthSessionEntryParameters['cafSessionTimeout'],
        'time_left' => $portAuthSessionEntryParameters['cafSessionTimeLeft'],
        'method' => $session_info['method'],
    ]);

    // save valid ids
    $port_nac_ids[] = $port_nac->ports_nac_id;
}


// delete old entries
$count = \LibreNMS\DB\Eloquent::DB()->table('ports_nac')->whereNotIn('ports_nac_id', $port_nac_ids)->delete();
d_echo('Deleted ' . $count, str_repeat('-', $count));
//    \App\Models\PortsNac::whereNotIn('ports_nac_id', $port_nac_ids)->get()->each->delete(); // alternate delete to trigger model events


unset($port_nac_ids, $ports_map, $portAuthSessionEntry, $cafSessionMethodsInfoEntry, $port_nac);
