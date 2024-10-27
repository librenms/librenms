<?php
/**
 * nokia-isam.inc.php
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
 * @copyright  2024 Vitali Kari and Rinse Kloek
 * @author     Vitali Kari <vitali.kari@gmail.com>
 * @author     Rinse Kloek <rinse@kindes.nl>
 */

$isam_port_table = [
    '94380032' => '1/1/1',
    '94445568' => '1/1/2',
    '94511104' => '1/1/3',
    '94576640' => '1/1/4',
    '94642176' => '1/1/5',
    '94707712' => '1/1/6',
    '94773248' => '1/1/7',
    '94838784' => '1/1/8',
    '94904320' => '1/1/9',
    '94969856' => '1/1/10',
    '95035392' => '1/1/11',
    '95100928' => '1/1/12',
    '95166464' => '1/1/13',
    '95232000' => '1/1/14',
    '95297536' => '1/1/15',
    '95363072' => '1/1/16',
    '127934464' => '1/2/1',
    '128000000' => '1/2/2',
    '128065536' => '1/2/3',
    '128131072' => '1/2/4',
    '128196608' => '1/2/5',
    '128262144' => '1/2/6',
    '128327680' => '1/2/7',
    '128393216' => '1/2/8',
    '128458752' => '1/2/9',
    '128524288' => '1/2/10',
    '128589824' => '1/2/11',
    '128655360' => '1/2/12',
    '128720896' => '1/2/13',
    '128786432' => '1/2/14',
    '128851968' => '1/2/15',
    '128917504' => '1/2/16',
    '161488896' => '1/3/1',
    '161554432' => '1/3/2',
    '161619968' => '1/3/3',
    '161685504' => '1/3/4',
    '161751040' => '1/3/5',
    '161816576' => '1/3/6',
    '161882112' => '1/3/7',
    '161947648' => '1/3/8',
    '162013184' => '1/3/9',
    '162078720' => '1/3/10',
    '162144256' => '1/3/11',
    '162209792' => '1/3/12',
    '162275328' => '1/3/13',
    '162340864' => '1/3/14',
    '162406400' => '1/3/15',
    '162471936' => '1/3/16',
    '195043328' => '1/4/1',
    '195108864' => '1/4/2',
    '195174400' => '1/4/3',
    '195239936' => '1/4/4',
    '195305472' => '1/4/5',
    '195371008' => '1/4/6',
    '195436544' => '1/4/7',
    '195502080' => '1/4/8',
    '195567616' => '1/4/9',
    '195633152' => '1/4/10',
    '195698688' => '1/4/11',
    '195764224' => '1/4/12',
    '195829760' => '1/4/13',
    '195895296' => '1/4/14',
    '195960832' => '1/5/15',
    '196026368' => '1/5/16',
    '228597760' => '1/5/1',
    '228663296' => '1/5/2',
    '228728832' => '1/5/3',
    '228794368' => '1/5/4',
    '228859904' => '1/5/5',
    '228925440' => '1/5/6',
    '228990976' => '1/5/7',
    '229056512' => '1/5/8',
    '229122048' => '1/5/9',
    '229187584' => '1/5/10',
    '229253120' => '1/5/11',
    '229318656' => '1/5/12',
    '229384192' => '1/5/13',
    '229449728' => '1/5/14',
    '229515264' => '1/5/15',
    '229580800' => '1/5/16',
    '262152192' => '1/6/1',
    '262217728' => '1/6/2',
    '262283264' => '1/6/3',
    '262348800' => '1/6/4',
    '262414336' => '1/6/5',
    '262479872' => '1/6/6',
    '262545408' => '1/6/7',
    '262610944' => '1/6/8',
    '262676480' => '1/6/9',
    '262742016' => '1/6/10',
    '262807552' => '1/6/11',
    '262873088' => '1/6/12',
    '262938624' => '1/6/13',
    '263004160' => '1/6/14',
    '263069696' => '1/6/15',
    '263135232' => '1/6/16'
];
// Get Nokia Port ID
function getNokiaIsamPortIndex($id) {
    // Base values for each group (1/1/x, 1/2/x, etc.)
    $baseId = 94380032;
    $portsPerGroup = 16;
    $groupDifference = 33554432;  // Difference in ID between groups (e.g., 1/1/x to 1/2/x)

    // Calculate the group (1/1, 1/2, etc.)
    $group = 1 + intval(($id - $baseId) / $groupDifference);

    // Calculate the port within the group (1 to 16)
    $port = 1 + (($id - $baseId) % $groupDifference) / ($groupDifference / $portsPerGroup);

    // Return the port index as a formatted string
    return "1/$group/$port";
}
/*
// TODO
// The In and OutOctets are not normal counters from ifMIB for the PON ports
// After you have enabled the pmcollect for pon utilization you can get the traffic value from
// They need to be walked from .1.3.6.1.4.1.637.61.1.35.21.57.1.$metric.$isam_port_id 
// $isam_port_id  see array above
// $metric        see array below
$metrics = [
    2 => 'OltSideUtilUcastDown',
    4 => 'OltSideUtilMcastDown',
    5 => 'OltSideBcastUtilDown',
    6 => 'OltSideUtilDown',
    7 => 'OltSideUtilUp',
    11 => 'OltSideBcastBytesDown',
    12 => 'OltSideOctetsUp',
    13 => 'OltSideOctetsUp',
    14 => 'OltSideDroppedBytesDown',
    15 => 'OltSideDroppedBytesUp',
    16 => 'OltNumOnts',
    25 => 'OltSidePacketsDown',
    26 => 'OltSidePacketsUp',
    27 => 'OltSidePacketsDropDown',
    28 => 'OltSidePacketsDropUp'
];

#$pon_port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.637.61.1.35.21.57.1', [], 'ITF-MIB-EXT', 'nokia-isam' );
$pon_port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.637.61.1.35.21.57.1', [] ) ;
foreach($pon_port_stats as $index => $port_stats) { 
    $metric     = explode('.', $index)[12];
    $port_index = explode('.', $index)[13];
    $val        = $port_stats['iso'];
    $val        = hexdec(str_replace(' ', '', $port_stats['iso']));
    if ($val > 0) {
        echo($port_index. ":".$metrics[$metric] . " = " .  $val. PHP_EOL);
    }

}
 */

// Use proprietary asamIfExtCustomerId as ifAlias for Nokia ISAM Plattform. The default IF-MIB fields are here quite meaningless
$isam_port_stats = snmpwalk_cache_oid($device, 'asamIfExtCustomerId', [], 'ITF-MIB-EXT', 'nokia-isam');
foreach ($isam_port_stats as $index => $value) {
   $port_stats[$index]['ifAlias'] = $isam_port_stats[$index]['asamIfExtCustomerId'];
}
// Use the PON Port ID as ifDescr as it makes more sense
// Use the static translation table 
foreach ($port_stats as $index => $port) {
    if (isset($port['ifType']) && $port['ifType'] == 'gpon'){
        $port_stats[$index]['ifDescr'] = 'PON ' . $isam_port_table[$index];
    }	
}

// Now also walk the IHUB context for prots
// Store the current context and set context to the extra context(s) we want to walk
$old_context_name = $device['context_name'];
$device['context_name'] = "ihub";


// Now do the same as in ports.inc full ports
$ihub_port_stats = snmpwalk_cache_oid($device, 'ifXEntry', $ihub_port_stats, 'IF-MIB');
$hc_test = array_slice($ihub_port_stats, 0, 1);
// If the device doesn't have ifXentry data, fetch ifEntry instead.
if (! is_numeric($hc_test[0]['ifHCInOctets'] ?? null) || ! is_numeric($hc_test[0]['ifHighSpeed'] ?? null)) {
    $ifEntrySnmpFlags = ['-OQUst'];
    if ($device['os'] == 'bintec-beip-plus') {
        $ifEntrySnmpFlags = ['-OQUst', '-Cc'];
    }
    $ihub_port_stats = snmpwalk_cache_oid($device, 'ifEntry', $ihub_port_stats, 'IF-MIB', null, $ifEntrySnmpFlags);
} else {
    // For devices with ifXentry data, only specific ifEntry keys are fetched to reduce SNMP load
    foreach ($ifmib_oids as $oid) {
        echo "$oid ";
        $ihub_port_stats = snmpwalk_cache_oid($device, $oid, $ihub_port_stats, 'IF-MIB', null, '-OQUst');
    }
}

// Use proprietary asamIfExtCustomerId as ifAlias for Nokia ISAM Plattform. The default IF-MIB fields are here quite meaningless
$isam_port_stats = snmpwalk_cache_oid($device, 'asamIfExtCustomerId', [], 'ITF-MIB-EXT', 'nokia-isam');
foreach ($isam_port_stats as $index => $value) {
    $ihub_port_stats[$index]['ifAlias'] = $isam_port_stats[$index]['asamIfExtCustomerId'];
}
$port_stats = array_merge($port_stats, $ihub_port_stats);

$device['context_name'] = $old_context_name;
unset($old_context_name);
unset($isam_ports_stats);
