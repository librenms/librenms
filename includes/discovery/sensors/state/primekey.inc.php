<?php
/*
 * LibreNMS state sensor for PrimeKey Hardware Appliances
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * The OIDs described here, for the EJBCA Appliance:
 * https://doc.primekey.com/ejbca-appliance/operations/webconf-configurator-of-hardware-appliance/monitoring
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2022 LibreNMS
 * @author     LibreNMS Contributors
 */

$oids = [
    0 => [
        'descr'      => 'Health VMs',
        'oid'        => 'PRIMEKEY-APPLIANCE-MIB::pkASfpVmStatus',
        'state_name' => 'HealthVMs',
        'group'      => 'Health of VMs',
        'states'     => [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'All OK'],
            ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'Some Inactive'],
        ],
    ],
    1 => [
        'descr'      => 'Health EJBCA',
        'oid'        => 'PRIMEKEY-APPLIANCE-MIB::pkAEJBCAHealth',
        'state_name' => 'HealthEjbca',
        'group'      => 'Health of VMs',
        'states'     => [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'All OK'],
            ['value' => 1, 'generic' => 3, 'graph' => 2, 'descr' => 'Not Running or Unhealthy'],
        ],
    ],
    2 => [
        'descr'      => 'Health SignServer',
        'oid'        => 'PRIMEKEY-APPLIANCE-MIB::pkASignServerHealth',
        'state_name' => 'HealthSignserver',
        'group'      => 'Health of VMs',
        'states'     => [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'All OK'],
            ['value' => 1, 'generic' => 3, 'graph' => 2, 'descr' => 'Not Running or Unhealthy'],
        ],
    ],
    3 => [
        'descr'      => 'Fan CPU',
        'oid'        => 'PRIMEKEY-APPLIANCE-MIB::pkASfpCpuFanStatus',
        'state_name' => 'FansCpu',
        'group'      => 'Fans',
        'states'     => [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'OK'],
            ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'Fail'],
        ],
    ],
    4 => [
        'descr'      => 'Fans System',
        'oid'        => 'PRIMEKEY-APPLIANCE-MIB::pkASfpSysFansStatus',
        'state_name' => 'FansSystem',
        'group'      => 'Fans',
        'states'     => [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'All OK'],
            ['value' => 1, 'generic' => 1, 'graph' => 1, 'descr' => 'Fail'],
        ],
    ],
    5 => [
        'descr'      => 'DB Storage',
        'oid'        => 'PRIMEKEY-APPLIANCE-MIB::pkAVdbStatus',
        'state_name' => 'DbStorage',
        'group'      => 'Database',
        'states'     => [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => '< 80% full'],
            ['value' => 1, 'generic' => 1, 'graph' => 1, 'descr' => '> 80% full'],
        ],
    ],
    6 => [
        'descr'      => 'DB Enum',
        'oid'        => 'PRIMEKEY-APPLIANCE-MIB::pkAClusterLocalGaleraState',
        'state_name' => 'DbEnum',
        'group'      => 'Database',
        // Galera node status can be reported as a number or a comment:
        //     - wsrep_local_state
        //        - PRIMEKEY-APPLIANCE-MIB::pkAClusterLocalGaleraState
        //        - .1.3.6.1.4.1.22408.1.1.2.1.8.99.108.117.115.116.101.114.52.1
        //     - wsrep_local_state_comment
        //        - PRIMEKEY-APPLIANCE-MIB::pkAClusterLocalGaleraStateString
        //        - .1.3.6.1.4.1.22408.1.1.2.1.8.99.108.117.115.116.101.114.53.1
        // This state table is based around an interpretation of how these two
        // variables relate to each other. See these links for more info:
        // https://github.com/codership/wsrep-API/blob/master/wsrep_api.h#L306 and
        // https://galeracluster.com/library/documentation/node-states.html#node-state-changes
        'states'     => [
            ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'Undefined'], //!< undefined state
            ['value' => 1, 'generic' => 1, 'graph' => 1, 'descr' => 'Joiner'],    //!< incomplete state, requested state transfer
            ['value' => 2, 'generic' => 0, 'graph' => 2, 'descr' => 'Donor'],     //!< complete state, donates state transfer
            ['value' => 3, 'generic' => 0, 'graph' => 3, 'descr' => 'Joined'],    //!< complete state
            ['value' => 4, 'generic' => 0, 'graph' => 4, 'descr' => 'Synced'],    //!< complete state, synchronized with group
            ['value' => 5, 'generic' => 2, 'graph' => 5, 'descr' => 'Error'],     //!< this and above is provider-specific error code
            ['value' => 6, 'generic' => 2, 'graph' => 6, 'descr' => 'Max'],
        ],
    ],
    7 => [
        'descr'      => 'RAID Health',
        'oid'        => 'PRIMEKEY-APPLIANCE-MIB::pkASfpRaidStatus',
        'state_name' => 'RaidHealth',
        'group'      => 'RAID',
        'states'     => [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Clean or Active'],
            ['value' => 1, 'generic' => 1, 'graph' => 1, 'descr' => 'Degraded'],
        ],
    ],
    8 => [
        'descr'      => 'HSM Enum',
        'state_name' => 'HsmEnum',
        'oid'        => 'PRIMEKEY-APPLIANCE-MIB::pkAHsmStatusEnum',
        'group'      => 'HSM',
        'states'     => [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'STATUS_is_OPER'],
            ['value' => 1, 'generic' => 1, 'graph' => 1, 'descr' => 'STATUS_is_MAINT'],
            ['value' => 2, 'generic' => 1, 'graph' => 2, 'descr' => 'STATUS_is_BOOT'],
            ['value' => 3, 'generic' => 2, 'graph' => 3, 'descr' => 'STATUS_is_ALARM'],
            ['value' => 4, 'generic' => 2, 'graph' => 4, 'descr' => 'STATUS_is_EXTERNALERASE'],
            ['value' => 5, 'generic' => 2, 'graph' => 5, 'descr' => 'STATUS_is_FAIL'],
            ['value' => 5, 'generic' => 3, 'graph' => 6, 'descr' => 'STATUS_is_OTHER'],
        ],
    ],
    9 => [
        'descr'      => 'HSM Healthy',
        'oid'        => 'PRIMEKEY-APPLIANCE-MIB::pkAHsmStatusBool',
        'state_name' => 'HsmHealthy',
        'group'      => 'HSM',
        'states'     => [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'OK'],
            ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'Fail'],
        ],
    ],
    10 => [
        'descr'      => 'Battery Int',
        'oid'        => 'PRIMEKEY-APPLIANCE-MIB::pkAHsmBatteryIntStatus',
        'state_name' => 'BatteryInt',
        'group'      => 'HSM',
        'states'     => [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'OK'],
            ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'Low or Fail'],
        ],
    ],
    11 => [
        'descr'      => 'Battery Ext',
        'oid'        => 'PRIMEKEY-APPLIANCE-MIB::pkAHsmBatteryExtStatus',
        'state_name' => 'BatteryExt',
        'group'      => 'HSM',
        'states'     => [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Ok or Absent'],
            ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'Low or Fail'],
        ],
    ],
];

$class = 'state';
$divisor = 1;
$multiplier = 1;
$low_limit = null;
$low_warn_limit = null;
$warn_limit = null;
$high_limit = null;
$poller_type = 'snmp';
$entPhysicalIndex = null;
$entPhysicalIndex_measured = null;
$user_func = null;

$transaction = snmp_get_multi_oid($device, array_column($oids, 'oid'));

foreach ($oids as $index => $entry) {
    $oid = $entry['oid'];
    $descr = $entry['descr'];
    $group = $entry['group'];

    $states = $entry['states'];
    $state_name = $entry['state_name'];

    if (oid_is_numeric($oid)) {
        $oid_num = $oid;
    } else {
        $oid_num = snmp_translate($oid, 'ALL', 'primekey', '-On');
    }

    create_state_index($state_name, $states);

    if (! empty($transaction)) {
        $current = $transaction[$oid_num];

        if (is_numeric($current)) {
            discover_sensor($valid['sensor'],
                            $class,
                            $device,
                            $oid_num,
                            $index,
                            $state_name,
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
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
unset($oids, $transaction, $class, $oid, $index, $state_name, $descr,
        $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit,
        $high_limit, $current, $poller_type, $entPhysicalIndex,
        $entPhysicalIndex_measured, $user_func, $group);
