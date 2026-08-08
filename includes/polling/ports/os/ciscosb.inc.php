<?php

/*
    LibreNMS port poller for CiscoSB
*/
$vlan_port_mode_state_array = SnmpQuery::walk('CISCOSB-vlan-MIB::vlanPortModeState')->pluck();
Log::debug('vlan_port_mode_state_array: ' . print_r($vlan_port_mode_state_array,true));

foreach ($vlan_port_mode_state_array as $index => $vlan_port_mode_state) {
    /* vlanPortModeState means:
      10 (General mode)
      11 (Access mode)
      12 (Trunk mode)
      13 (Private-VLAN permiscouos mode)
      14 (Private-VLAN host mode)
      15 (Customer)
          (according to "Cisco Business 350 Series Switches Administration Guide")
    */
    Log::debug(print_r($index,true) . '=>' . print_r($vlan_port_mode_state,true));
    if ($vlan_port_mode_state == 12 && isset($port_stats[$index])) {
        $port_stats[$index]['ifTrunk'] = 'dot1Q';
    }
}

