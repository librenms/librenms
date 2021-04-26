<?php

/*
 *
 * OIDs obtained from First Network Group Inc. DHCPatriot operations manual version 6.4.x
 * Found here: http://www.network1.net/products/dhcpatriot/documentation/PDFs/v64xmanual-rev1.pdf
 *
*/

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

$dhcp_networks_base_oid = '1.3.6.1.4.1.2021.50.110';
$pool_usage_base_oid = '1.3.6.1.4.1.2021.50.120';
$pool_size_base_oid = '1.3.6.1.4.1.2021.50.130';

$auth_dhcp_index = '1';
$standard_dhcp_index = '2';

$auth_dhcp_networks_descr = snmpwalk_array_num($device, $dhcp_networks_base_oid . '.' . $auth_dhcp_index, 2);
$standard_dhcp_networks_descr = snmpwalk_array_num($device, $dhcp_networks_base_oid . '.' . $standard_dhcp_index, 2);

if (empty($standard_dhcp_networks_descr) && ! empty($auth_dhcp_networks_descr)) {
    $dhcp_networks = $auth_dhcp_networks_descr;
}
if (empty($auth_dhcp_networks_descr) && ! empty($standard_dhcp_networks_descr)) {
    $dhcp_networks = $standard_dhcp_networks_descr;
}
if (! empty($auth_dhcp_networks_descr) && ! empty($standard_dhcp_networks_descr)) {
    $dhcp_networks = array_merge_recursive($auth_dhcp_networks_descr, $standard_dhcp_networks_descr);
}

$array_index = 0;

if (! empty($dhcp_networks[$dhcp_networks_base_oid])) {
    foreach ($dhcp_networks[$dhcp_networks_base_oid] as $dhcp_type_index => $ignore_this) {
        if (! empty($dhcp_networks[$dhcp_networks_base_oid][$dhcp_type_index])) {
            foreach ($dhcp_networks[$dhcp_networks_base_oid][$dhcp_type_index] as $index => $entry) {
                $description = (explode('[', $entry));
                $data_array[$array_index]['index'] = $index;
                if ($dhcp_type_index === intval($auth_dhcp_index)) {
                    $data_array[$array_index]['type'] = 'dhcpatriotAuthDHCP';
                    $data_array[$array_index]['group'] = 'Authenticated DHCP';
                }
                if ($dhcp_type_index === intval($standard_dhcp_index)) {
                    $data_array[$array_index]['type'] = 'dhcpatriotStandardDHCP';
                    $data_array[$array_index]['group'] = 'Standard DHCP';
                }
                $data_array[$array_index]['description'] = $description[0];
                $data_array[$array_index]['oid'] = '.' . $pool_usage_base_oid . '.' . $dhcp_type_index . '.' . $index;
                $data_array[$array_index]['size_oid'] = '.' . $pool_size_base_oid . '.' . $dhcp_type_index . '.' . $index;
                $array_index = $array_index + 1;
            }
        }
    }

    $merged_array = array_merge(array_column($data_array, 'oid'), array_column($data_array, 'size_oid'));
    $pool_data = snmp_get_multi_oid($device, $merged_array);

    foreach ($data_array as $key => $value) {
        $oid = $value['oid'];
        $index = $value['index'];
        $type = $value['type'];
        $divisor = $pool_data[$value['size_oid']];
        $descr = $value['description'] . ' (' . $pool_data[$value['oid']] . '/' . $divisor . ')';
        $current = (($pool_data[$value['oid']] / $divisor) * 100);
        $group = $value['group'];

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

unset($class, $oid, $index, $type, $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $current, $poller_type, $entPhysicalIndex, $entPhysicalIndex_measured, $user_func, $group, $dhcp_networks_base_oid, $pool_usage_base_oid, $pool_size_base_oid, $auth_dhcp_index, $standard_dhcp_index, $auth_dhcp_networks_descr, $standard_dhcp_networks_descr, $dhcp_networks, $array_index, $description, $data_array, $merged_array, $pool_data);
