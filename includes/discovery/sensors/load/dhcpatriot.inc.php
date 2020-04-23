<?php

/*
 *
 * OIDs obtained from First Network Group Inc. DHCPatriot operations manual version 6.4.x
 * Found here: http://www.network1.net/products/dhcpatriot/documentation/PDFs/v64xmanual-rev1.pdf
 *
*/

/* Set base OID values */
$dhcp_networks_base_oid = '1.3.6.1.4.1.2021.50.110';
$pool_usage_base_oid = '1.3.6.1.4.1.2021.50.120';
$pool_size_base_oid = '1.3.6.1.4.1.2021.50.130';

/* Set dhcp index values for authenticated and standard DHCP networks */
$auth_dhcp_index = '1';
$standard_dhcp_index = '2';

/* Get configured networks from DHCPatriot and merge the arrays */
$auth_dhcp_networks = snmpwalk_array_num($device, $dhcp_networks_base_oid . '.' . $auth_dhcp_index, 2);
$standard_dhcp_networks = snmpwalk_array_num($device, $dhcp_networks_base_oid . '.' . $standard_dhcp_index, 2);

if (!is_array($standard_dhcp_networks) && is_array($auth_dhcp_networks) && !empty($auth_dhcp_networks)) {
    $dhcp_networks = $auth_dhcp_networks;
}
if (!is_array($auth_dhcp_networks) && is_array($standard_dhcp_networks) && !empty($standard_dhcp_networks)) {
    $dhcp_networks = $standard_dhcp_networks;
}
if (is_array($auth_dhcp_networks) && !empty($auth_dhcp_networks) && is_array($standard_dhcp_networks) && !empty($standard_dhcp_networks)) {
    $dhcp_networks = array_merge_recursive($auth_dhcp_networks, $standard_dhcp_networks);
}

/* Set discover_sensor variables that are the same regardless of DHCP type */
$class = 'load';
$multiplier = 100;
$low_limit = null;
$low_warn_limit = null;
$warn_limit = 95;
$high_limit = 100;
$poller_type = 'snmp';
$entPhysicalIndex = null;
$entPhysicalIndex_measured = null;
$user_func = null;

if (is_array($dhcp_networks) && !empty($dhcp_networks)) {
    if (is_array($dhcp_networks[$dhcp_networks_base_oid]) && !empty($dhcp_networks[$dhcp_networks_base_oid])) {
        /* Loop through the DHCP type tier of the array grabbing the dhcp_type_index for later use */
        foreach ($dhcp_networks[$dhcp_networks_base_oid] as $dhcp_type_index => $ignore_this) {
            if (is_array($dhcp_networks[$dhcp_networks_base_oid][$dhcp_type_index]) && !empty($dhcp_networks[$dhcp_networks_base_oid][$dhcp_type_index])) {
                /* Loop through the DHCP networks and set the network specific discover_sensor variables */
                foreach ($dhcp_networks[$dhcp_networks_base_oid][$dhcp_type_index] as $index => $entry) {
                    $oid = ('.' . $pool_usage_base_oid . '.' . $dhcp_type_index . '.' . $index);
                    $pool_size_oid = ('.' . $pool_size_base_oid . '.' . $dhcp_type_index . '.' . $index);
                    $pool_data = snmp_get_multi_oid($device, $oid . ' ' . $pool_size_oid);

                    if ($dhcp_type_index === intval($auth_dhcp_index)) {
                        $type = 'dhcpatriotAuthDHCP';
                        $group = 'Authenticated DHCP';
                    }

                    if ($dhcp_type_index === intval($standard_dhcp_index)) {
                        $type = 'dhcpatriotStandardDHCP';
                        $group = 'Standard DHCP';
                    }

                    $descr = explode('[', $entry);
                    $descr = $descr[0] . ' (' . $pool_data[$oid] . '/' . $pool_data[$pool_size_oid] . ')';
                    $divisor = $pool_data[$pool_size_oid];
                    $current = (($pool_data[$oid] / $pool_data[$pool_size_oid]) * 100);

                    discover_sensor(
                        $valid['sensor'],
                        $class,
                        $device,
                        $oid,
                        $index,
                        $type,
                        $descr,
                        $divisor,
                        $multiplier,
                        $low_limit,
                        $low_warn_limit,
                        $warn_limit,
                        $high_limit,
                        $current,
                        $poller_type,
                        $entPhysicalIndex,
                        $entPhysicalIndex_measured,
                        $user_func,
                        $group
                    );
                }
            }
        }
    }
}

unset($dhcp_networks_base_oid, $pool_usage_base_oid, $pool_size_base_oid, $auth_dhcp_index, $standard_dhcp_index, $auth_dhcp_networks, $standard_dhcp_networks, $dhcp_networks, $class, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $poller_type, $entPhysicalIndex, $entPhysicalIndex_measured, $user_func, $oid, $pool_size_oid, $pool_data, $type, $group, $descr, $divisor, $current);
