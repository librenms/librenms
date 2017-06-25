<?php

echo 'Comware:';

// find fdb entries, output like
// dot1qTpFdbPort[507][0:24:c4:fd:a1:c7] 1
$fdbPort_table = snmp_walk($device, 'dot1qTpFdbEntry', '-OqsX', 'Q-BRIDGE-MIB');

// find port ids, output like
// dot1dBasePortIfIndex[1] 1
$dot1dBasePortIfIndex = snmp_walk($device, 'dot1dBasePortIfIndex', '-OqsX', 'BRIDGE-MIB');

foreach (explode("\n", $dot1dBasePortIfIndex) as $dot1dBasePortIfIndex_entry) {
    if (!empty($dot1dBasePortIfIndex_entry)) {
        $port = explode(' ', $dot1dBasePortIfIndex_entry);
        $strTemp = explode('[', $port[0]);
        $portLocal = rtrim($strTemp[1], ']');
        $portid_dict[$portLocal] = $ifIndex_dict[$port[1]];
    }
}

foreach (explode("\n", $fdbPort_table) as $fdbPort_entry) {
    preg_match('~(?P<oid>\w+)\[(?P<vlan>\d+)]\[(?P<mac>[\w:-]+)]\s(?P<port>\d+)~', $fdbPort_entry, $matches);
    if (!empty($matches)) {
        $port = $matches['port'];
        $mac = $matches['mac'];
        $vlan = $matches['vlan'];
        $vlan_id = $vlans_dict[$vlan];
        echo " $vlan";

        if (!empty($mac)) {
            list($oct_1, $oct_2, $oct_3, $oct_4, $oct_5, $oct_6) = explode(':', $mac);
            $mac_address = zeropad($oct_1) . zeropad($oct_2) . zeropad($oct_3) . zeropad($oct_4) . zeropad($oct_5) . zeropad($oct_6);
            if (strlen($mac_address) != 12) {
                echo 'Mac Address padding failed';
                continue;
            } else {
                $dot1dBasePort = $port;
                $insert[$vlan_id][$mac_address]['port_id'] = $portid_dict[$dot1dBasePort];
                d_echo("vlan $vlan mac $mac_address port ($port) " . $portid_dict[$dot1dBasePort] . PHP_EOL);
            }
        } // end if on empty mac
    } // end if on matches
} // end loop on fdbPort_entry

echo PHP_EOL;
