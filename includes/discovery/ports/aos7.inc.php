<?php
/**
 * aos7.inc.php
 *
 * AOS7 port support:
 * A AOS7 device need the named_context flag for SnmpQuery if VRF's is involved,
 * to get IF-MIB::ifDescr and so on.
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
 * @author     Tamas Baumgartner-Kis <tamas.baumgartner-kis@rz.uni-freiburg.de>
 */

// get the vrfs to iterate over
$aos7VrfTable = snmpwalk_cache_oid($device, 'alaVirtualRouterNameTable', [], 'ALCATEL-IND1-VIRTUALROUTER-MIB');

// get the IF-MIB::ifDescr and so on with the named_context flag for the SnmpQuery
foreach ($aos7VrfTable as $vrf_name => $vrf_data) {
    $aos7ifDescr = SnmpQuery::context($vrf_name, null, $descrSnmpFlags)->walk('IF-MIB::ifDescr')->table(1);
    $aos7ifName = SnmpQuery::context($vrf_name, null, '-OQUs')->walk('IF-MIB::ifName')->table(1);
    $aos7ifAlias = SnmpQuery::context($vrf_name, null, '-OQUs')->walk('IF-MIB::ifAlias')->table(1);
    $aos7ifType = SnmpQuery::context($vrf_name, null, $typeSnmpFlags)->walk('IF-MIB::ifType')->table(1);
    $aos7ifOperStatus = SnmpQuery::context($vrf_name, null, $operStatusSnmpFlags)->walk('IF-MIB::ifOperStatus')->table(1);

    foreach ($aos7ifDescr as $if_index => $data) {
        d_echo("TBKDescr: $if_index = " . $data['ifDescr']);
        $port_stats[$if_index]['ifDescr'] = $data['ifDescr'];
    }

    foreach ($aos7ifName as $if_index => $data) {
        d_echo("TBKName: $if_index = " . $data['ifName']);
        $port_stats[$if_index]['ifName'] = $data['ifName'];
    }

    foreach ($aos7ifAlias as $if_index => $data) {
        d_echo("TBKAlias: $if_index = " . $data['ifAlias']);
        $port_stats[$if_index]['ifAlias'] = $data['ifAlias'];
    }

    foreach ($aos7ifType as $if_index => $data) {
        d_echo("TBKType: $if_index = " . $data['ifType']);
        $port_stats[$if_index]['ifType'] = $data['ifType'];
    }

    foreach ($aos7ifOperStatus as $if_index => $data) {
        d_echo("TBKOperStatus: $if_index = " . $data['ifOperStatus']);
        $port_stats[$if_index]['ifOperStatus'] = $data['ifOperStatus'];
    }
}

unset($aos7VrfTable);
unset($aos7ifDescr);
unset($aos7ifName);
unset($aos7ifAlias);
unset($aos7ifType);
unset($aos7ifOperStatus);
