<?php

// Build SNMP Cache Array
$data_oids = array(
    'ifName',
    'ifDescr',
    'ifAlias',
    'ifAdminStatus',
    'ifOperStatus',
    'ifMtu',
    'ifSpeed',
    'ifHighSpeed',
    'ifType',
    'ifPhysAddress',
    'ifPromiscuousMode',
    'ifConnectorPresent',
    'ifDuplex',
    'ifTrunk',
    'ifVlan',
);

$stat_oids = array(
    'ifInErrors',
    'ifOutErrors',
    'ifInUcastPkts',
    'ifOutUcastPkts',
    'ifInNUcastPkts',
    'ifOutNUcastPkts',
    'ifHCInMulticastPkts',
    'ifHCInBroadcastPkts',
    'ifHCOutMulticastPkts',
    'ifHCOutBroadcastPkts',
    'ifInOctets',
    'ifOutOctets',
    'ifHCInOctets',
    'ifHCOutOctets',
    'ifInDiscards',
    'ifOutDiscards',
    'ifInUnknownProtos',
    'ifInBroadcastPkts',
    'ifOutBroadcastPkts',
    'ifInMulticastPkts',
    'ifOutMulticastPkts',
);

$stat_oids_db = array(
    'ifInOctets',
    'ifOutOctets',
    'ifInErrors',
    'ifOutErrors',
    'ifInUcastPkts',
    'ifOutUcastPkts',
);

$stat_oids_db_extended = array(
    'ifInNUcastPkts',
    'ifOutNUcastPkts',
    'ifInDiscards',
    'ifOutDiscards',
    'ifInUnknownProtos',
    'ifInBroadcastPkts',
    'ifOutBroadcastPkts',
    'ifInMulticastPkts',
    'ifOutMulticastPkts',
);

// From above for DB
$etherlike_oids = array(
    'dot3StatsAlignmentErrors',
    'dot3StatsFCSErrors',
    'dot3StatsSingleCollisionFrames',
    'dot3StatsMultipleCollisionFrames',
    'dot3StatsSQETestErrors',
    'dot3StatsDeferredTransmissions',
    'dot3StatsLateCollisions',
    'dot3StatsExcessiveCollisions',
    'dot3StatsInternalMacTransmitErrors',
    'dot3StatsCarrierSenseErrors',
    'dot3StatsFrameTooLongs',
    'dot3StatsInternalMacReceiveErrors',
    'dot3StatsSymbolErrors',
);

$cisco_oids = array(
    'locIfHardType',
    'locIfInRunts',
    'locIfInGiants',
    'locIfInCRC',
    'locIfInFrame',
    'locIfInOverrun',
    'locIfInIgnored',
    'locIfInAbort',
    'locIfCollisions',
    'locIfInputQueueDrops',
    'locIfOutputQueueDrops',
);

$pagp_oids = array(
    'pagpOperationMode',
    'pagpPortState',
    'pagpPartnerDeviceId',
    'pagpPartnerLearnMethod',
    'pagpPartnerIfIndex',
    'pagpPartnerGroupIfIndex',
    'pagpPartnerDeviceName',
    'pagpEthcOperationMode',
    'pagpDeviceId',
    'pagpGroupIfIndex',
);

$ifmib_oids = array_merge($data_oids, $stat_oids);

$ifmib_oids = array(
    'ifEntry',
    'ifXEntry',
);

echo 'Caching Oids: ';
foreach ($ifmib_oids as $oid) {
    echo "$oid ";
    $port_stats = snmpwalk_cache_oid($device, $oid, $port_stats, 'IF-MIB');
}

if ($config['enable_ports_etherlike']) {
    echo 'dot3Stats ';
    $port_stats = snmpwalk_cache_oid($device, 'dot3StatsEntry', $port_stats, 'EtherLike-MIB');
}
else {
    echo 'dot3StatsDuplexStatus';
    $port_stats = snmpwalk_cache_oid($device, 'dot3StatsDuplexStatus', $port_stats, 'EtherLike-MIB');
}

if ($config['enable_ports_adsl']) {
    $device['adsl_count'] = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE `device_id` = ? AND `ifType` = 'adsl'", array($device['device_id']));
}

if ($device['adsl_count'] > '0') {
    echo 'ADSL ';
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.1.1', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.2.1', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.3.1', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.4.1', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.5.1', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.6.1.1', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.6.1.2', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.6.1.3', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.6.1.4', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.6.1.5', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.6.1.6', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.6.1.7', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.6.1.8', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.7.1.1', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.7.1.2', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.7.1.3', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.7.1.4', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.7.1.5', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.7.1.6', $port_stats, 'ADSL-LINE-MIB');
    $port_stats = snmpwalk_cache_oid($device, '.1.3.6.1.2.1.10.94.1.1.7.1.7', $port_stats, 'ADSL-LINE-MIB');
}//end if

if ($config['enable_ports_poe']) {
    $port_stats = snmpwalk_cache_oid($device, 'pethPsePortEntry', $port_stats, 'POWER-ETHERNET-MIB');
    $port_stats = snmpwalk_cache_oid($device, 'cpeExtPsePortEntry', $port_stats, 'CISCO-POWER-ETHERNET-EXT-MIB');
}

// FIXME This probably needs re-enabled. We need to clear these things when they get unset, too.
// foreach ($etherlike_oids as $oid) { $port_stats = snmpwalk_cache_oid($device, $oid, $port_stats, "EtherLike-MIB"); }
// foreach ($cisco_oids as $oid)     { $port_stats = snmpwalk_cache_oid($device, $oid, $port_stats, "OLD-CISCO-INTERFACES-MIB"); }
// foreach ($pagp_oids as $oid)      { $port_stats = snmpwalk_cache_oid($device, $oid, $port_stats, "CISCO-PAGP-MIB"); }
if ($device['os_group'] == 'cisco') {
    $port_stats = snmp_cache_portIfIndex($device, $port_stats);
    $port_stats = snmp_cache_portName($device, $port_stats);
    foreach ($pagp_oids as $oid) {
        $port_stats = snmpwalk_cache_oid($device, $oid, $port_stats, 'CISCO-PAGP-MIB');
    }

    $data_oids[] = 'portName';

    // Grab data to put ports into vlans or make them trunks
    // FIXME we probably shouldn't be doing this from the VTP MIB, right?
    $port_stats = snmpwalk_cache_oid($device, 'vmVlan', $port_stats, 'CISCO-VLAN-MEMBERSHIP-MIB');
    $port_stats = snmpwalk_cache_oid($device, 'vlanTrunkPortEncapsulationOperType', $port_stats, 'CISCO-VTP-MIB');
    $port_stats = snmpwalk_cache_oid($device, 'vlanTrunkPortNativeVlan', $port_stats, 'CISCO-VTP-MIB');
}
else {
    $port_stats = snmpwalk_cache_oid($device, 'dot1qPortVlanTable', $port_stats, 'Q-BRIDGE-MIB');
}//end if

$polled = time();

// End Building SNMP Cache Array
d_echo($port_stats);

// Build array of ports in the database
// FIXME -- this stuff is a little messy, looping the array to make an array just seems wrong. :>
// -- i can make it a function, so that you don't know what it's doing.
// -- $ports = adamasMagicFunction($ports_db); ?
// select * doesn't do what we want if multiple tables have the same column name -- last one wins :/
$ports_db = dbFetchRows('SELECT *, `ports_statistics`.`port_id` AS `ports_statistics_port_id`, `ports`.`port_id` AS `port_id` FROM `ports` LEFT OUTER JOIN `ports_statistics` ON `ports`.`port_id` = `ports_statistics`.`port_id` WHERE `ports`.`device_id` = ?', array($device['device_id']));

foreach ($ports_db as $port) {
    $ports[$port['ifIndex']] = $port;
}

// New interface detection
foreach ($port_stats as $ifIndex => $port) {
    if (is_port_valid($port, $device)) {
        echo 'valid';
        if (!is_array($ports[$port['ifIndex']])) {
            $port_id                 = dbInsert(array('device_id' => $device['device_id'], 'ifIndex' => $ifIndex), 'ports');
            dbInsert(array('port_id' => $port_id), 'ports_statistics');
            $ports[$port['ifIndex']] = dbFetchRow('SELECT * FROM `ports` WHERE `port_id` = ?', array($port_id));
            echo 'Adding: '.$port['ifName'].'('.$ifIndex.')('.$ports[$port['ifIndex']]['port_id'].')';
            // print_r($ports);
        }
        else if ($ports[$ifIndex]['deleted'] == '1') {
            dbUpdate(array('deleted' => '0'), 'ports', '`port_id` = ?', array($ports[$ifIndex]['port_id']));
            $ports[$ifIndex]['deleted'] = '0';
        }
        if ($ports[$ifIndex]['ports_statistics_port_id'] === null) {
            // in case the port was created before we created the table
            dbInsert(array('port_id' => $ports[$ifIndex]['port_id']), 'ports_statistics');
        }
    }
    else {
        if ($ports[$port['ifIndex']]['deleted'] != '1') {
            dbUpdate(array('deleted' => '1'), 'ports', '`port_id` = ?', array($ports[$ifIndex]['port_id']));
            $ports[$ifIndex]['deleted'] = '1';
        }
    }
}

// End New interface detection
echo "\n";
// Loop ports in the DB and update where necessary
foreach ($ports as $port) {
    echo 'Port '.$port['ifDescr'].'('.$port['ifIndex'].') ';
    if ($port_stats[$port['ifIndex']] && $port['disabled'] != '1') {
        // Check to make sure Port data is cached.
        $this_port = &$port_stats[$port['ifIndex']];

        if ($device['os'] == 'vmware' && preg_match('/Device ([a-z0-9]+) at .*/', $this_port['ifDescr'], $matches)) {
            $this_port['ifDescr'] = $matches[1];
        }

        $polled_period = ($polled - $port['poll_time']);

        $port['update'] = array();
        $port['update_extended'] = array();
        $port['state']  = array();

        if ($config['slow_statistics'] == true) {
            $port['update']['poll_time']   = $polled;
            $port['update']['poll_prev']   = $port['poll_time'];
            $port['update']['poll_period'] = $polled_period;
        }

        // Copy ifHC[In|Out]Octets values to non-HC if they exist
        if ($this_port['ifHCInOctets'] > 0 && is_numeric($this_port['ifHCInOctets']) && $this_port['ifHCOutOctets'] > 0 && is_numeric($this_port['ifHCOutOctets'])) {
            echo 'HC ';
            $this_port['ifInOctets']  = $this_port['ifHCInOctets'];
            $this_port['ifOutOctets'] = $this_port['ifHCOutOctets'];
        }

        if ($device['os'] === 'airos-af' && $port['ifAlias'] === 'eth0') {
            $airos_stats = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.41112.1.3.3.1', $airos_stats, 'UBNT-AirFIBER-MIB');
            $this_port['ifInOctets'] = $airos_stats[1]['rxOctetsOK'];
            $this_port['ifOutOctets'] = $airos_stats[1]['txOctetsOK'];
            $this_port['ifInErrors'] = $airos_stats[1]['rxErroredFrames'];
            $this_port['ifOutErrors'] = $airos_stats[1]['txErroredFrames'];
            $this_port['ifInBroadcastPkts'] = $airos_stats[1]['rxValidBroadcastFrames'];
            $this_port['ifOutBroadcastPkts'] = $airos_stats[1]['txValidBroadcastFrames'];
            $this_port['ifInMulticastPkts'] = $airos_stats[1]['rxValidMulticastFrames'];
            $this_port['ifOutMulticastPkts'] = $airos_stats[1]['txValidMulticastFrames'];
            $this_port['ifInUcastPkts'] = $airos_stats[1]['rxValidUnicastFrames'];
            $this_port['ifOutUcastPkts'] = $airos_stats[1]['txValidUnicastFrames'];
            $ports['update']['ifInOctets'] = $airos_stats[1]['rxOctetsOK'];
            $ports['update']['ifOutOctets'] = $airos_stats[1]['txOctetsOK'];
            $ports['update']['ifInErrors'] = $airos_stats[1]['rxErroredFrames'];
            $ports['update']['ifOutErrors'] = $airos_stats[1]['txErroredFrames'];
            $ports['update']['ifInBroadcastPkts'] = $airos_stats[1]['rxValidBroadcastFrames'];
            $ports['update']['ifOutBroadcastPkts'] = $airos_stats[1]['txValidBroadcastFrames'];
            $ports['update']['ifInMulticastPkts'] = $airos_stats[1]['rxValidMulticastFrames'];
            $ports['update']['ifOutMulticastPkts'] = $airos_stats[1]['txValidMulticastFrames'];
            $ports['update']['ifInUcastPkts'] = $airos_stats[1]['rxValidUnicastFrames'];
            $ports['update']['ifOutUcastPkts'] = $airos_stats[1]['txValidUnicastFrames'];
        }

        // rewrite the ifPhysAddress
        if (strpos($this_port['ifPhysAddress'], ':')) {
            list($a_a, $a_b, $a_c, $a_d, $a_e, $a_f) = explode(':', $this_port['ifPhysAddress']);
            $this_port['ifPhysAddress']              = zeropad($a_a).zeropad($a_b).zeropad($a_c).zeropad($a_d).zeropad($a_e).zeropad($a_f);
        }

        if (is_numeric($this_port['ifHCInBroadcastPkts']) && is_numeric($this_port['ifHCOutBroadcastPkts']) && is_numeric($this_port['ifHCInMulticastPkts']) && is_numeric($this_port['ifHCOutMulticastPkts']) && $device['os'] !== 'ciscosb') {
            echo 'HC ';
            $this_port['ifInBroadcastPkts']  = $this_port['ifHCInBroadcastPkts'];
            $this_port['ifOutBroadcastPkts'] = $this_port['ifHCOutBroadcastPkts'];
            $this_port['ifInMulticastPkts']  = $this_port['ifHCInMulticastPkts'];
            $this_port['ifOutMulticastPkts'] = $this_port['ifHCOutMulticastPkts'];
        }

        // Overwrite ifSpeed with ifHighSpeed if it's over 1G
        if (is_numeric($this_port['ifHighSpeed']) && ($this_port['ifSpeed'] > '1000000000' || $this_port['ifSpeed'] == 0)) {
            echo 'HighSpeed ';
            $this_port['ifSpeed'] = ($this_port['ifHighSpeed'] * 1000000);
        }

        // Overwrite ifDuplex with dot3StatsDuplexStatus if it exists
        if (isset($this_port['dot3StatsDuplexStatus'])) {
            echo 'dot3Duplex ';
            $this_port['ifDuplex'] = $this_port['dot3StatsDuplexStatus'];
        }

        // Set VLAN and Trunk from Cisco
        if (isset($this_port['vlanTrunkPortEncapsulationOperType']) && $this_port['vlanTrunkPortEncapsulationOperType'] != 'notApplicable') {
            $this_port['ifTrunk'] = $this_port['vlanTrunkPortEncapsulationOperType'];
            if (isset($this_port['vlanTrunkPortNativeVlan'])) {
                $this_port['ifVlan'] = $this_port['vlanTrunkPortNativeVlan'];
            }
        }

        if (isset($this_port['vmVlan'])) {
            $this_port['ifVlan'] = $this_port['vmVlan'];
        }

        // Set VLAN and Trunk from Q-BRIDGE-MIB
        if (!isset($this_port['ifVlan']) && isset($this_port['dot1qPvid'])) {
            $this_port['ifVlan'] = $this_port['dot1qPvid'];
        }

        // FIXME use $q_bridge_mib[$this_port['ifIndex']] to see if it is a trunk (>1 array count)
        echo 'VLAN == '.$this_port['ifVlan'];

	// When devices do not provide ifAlias data, populate with ifDescr data if configured
        if ($this_port['ifAlias'] == '' || $this_port['ifAlias'] == NULL) {
            $this_port['ifAlias'] = $this_port['ifDescr'];
            d_echo('Using ifDescr as ifAlias');
        }

        // Update IF-MIB data
        $tune_port = false;
        foreach ($data_oids as $oid) {

            if ($oid == 'ifAlias') {
                if (get_dev_attrib($device, 'ifName:'.$port['ifName'], 1)) {
                    $this_port['ifAlias'] = $port['ifAlias'];
                }
            }

            if ($port[$oid] != $this_port[$oid] && !isset($this_port[$oid])) {
                $port['update'][$oid] = array('NULL');
                log_event($oid.': '.$port[$oid].' -> NULL', $device, 'interface', $port['port_id']);
                if ($debug) {
                    d_echo($oid.': '.$port[$oid].' -> NULL ');
                }
                else {
                    echo $oid.' ';
                }
            }
            else if ($port[$oid] != $this_port[$oid]) {
                $port_tune = get_dev_attrib($device, 'ifName_tune:'.$port['ifName']);
                $device_tune = get_dev_attrib($device,'override_rrdtool_tune');
                if ($port_tune == "true" ||
                    ($device_tune == "true" && $port_tune != 'false') || 
                    ($config['rrdtool_tune'] == "true" && $port_tune != 'false' && $device_tune != 'false')) {
                    if ($oid == 'ifSpeed') {
                        $tune_port = true;
                    }
                }
                $port['update'][$oid] = $this_port[$oid];
                log_event($oid.': '.$port[$oid].' -> '.$this_port[$oid], $device, 'interface', $port['port_id']);
                if ($debug) {
                    d_echo($oid.': '.$port[$oid].' -> '.$this_port[$oid].' ');
                }
                else {
                    echo $oid.' ';
                }
            }
        }//end foreach

        // Parse description (usually ifAlias) if config option set
        if (isset($config['port_descr_parser']) && is_file($config['install_dir'].'/'.$config['port_descr_parser'])) {
            $port_attribs = array(
                'type',
                'descr',
                'circuit',
                'speed',
                'notes',
            );

            include $config['install_dir'].'/'.$config['port_descr_parser'];

            foreach ($port_attribs as $attrib) {
                $attrib_key = 'port_descr_'.$attrib;
                if ($port_ifAlias[$attrib] != $port[$attrib_key]) {
                    if (!isset($port_ifAlias[$attrib])) {
                        $port_ifAlias[$attrib] = array('NULL');
                        $log_port              = 'NULL';
                    }
                    else {
                        $log_port = $port_ifAlias[$attrib];
                    }

                    $port['update'][$attrib_key] = $port_ifAlias[$attrib];
                    log_event($attrib.': '.$port[$attrib_key].' -> '.$log_port, $device, 'interface', $port['port_id']);
                    unset($log_port);
                }
            }
        }//end if

        // End parse ifAlias
        // Update IF-MIB metrics
        $_stat_oids = array_merge($stat_oids_db, $stat_oids_db_extended);
        foreach ($_stat_oids as $oid) {
            $port_update = 'update';
            $extended_metric = !in_array($oid, $stat_oids_db, true);
            if ($extended_metric) {
                $port_update = 'update_extended';
            }

            if ($config['slow_statistics'] == true) {
                $port[$port_update][$oid]         = $this_port[$oid];
                $port[$port_update][$oid.'_prev'] = $port[$oid];
            }

            $oid_prev = $oid.'_prev';
            if (isset($port[$oid])) {
                $oid_diff = ($this_port[$oid] - $port[$oid]);
                $oid_rate = ($oid_diff / $polled_period);
                if ($oid_rate < 0) {
                    $oid_rate = '0';
                    echo "negative $oid";
                }

                $port['stats'][$oid.'_rate'] = $oid_rate;
                $port['stats'][$oid.'_diff'] = $oid_diff;

                if ($config['slow_statistics'] == true) {
                    $port[$port_update][$oid.'_rate']  = $oid_rate;
                    $port[$port_update][$oid.'_delta'] = $oid_diff;
                }

                d_echo("\n $oid ($oid_diff B) $oid_rate Bps $polled_period secs\n");
            }//end if
        }//end foreach

        if ($config['debug_port'][$port['port_id']]) {
            $port_debug  = $port['port_id'].'|'.$polled.'|'.$polled_period.'|'.$this_port['ifHCInOctets'].'|'.$this_port['ifHCOutOctets'];
            $port_debug .= '|'.$port['stats']['ifInOctets_rate'].'|'.$port['stats']['ifOutOctets_rate']."\n";
            file_put_contents('/tmp/port_debug.txt', $port_debug, FILE_APPEND);
            echo 'Wrote port debugging data';
        }

        $port['stats']['ifInBits_rate']  = round(($port['stats']['ifInOctets_rate'] * 8));
        $port['stats']['ifOutBits_rate'] = round(($port['stats']['ifOutOctets_rate'] * 8));

        // If we have a valid ifSpeed we should populate the stats for checking.
        if (is_numeric($this_port['ifSpeed'])) {
            $port['stats']['ifInBits_perc']  = round(($port['stats']['ifInBits_rate'] / $this_port['ifSpeed'] * 100));
            $port['stats']['ifOutBits_perc'] = round(($port['stats']['ifOutBits_rate'] / $this_port['ifSpeed'] * 100));
        }

        echo 'bps('.formatRates($port['stats']['ifInBits_rate']).'/'.formatRates($port['stats']['ifOutBits_rate']).')';
        echo 'bytes('.formatStorage($port['stats']['ifInOctets_diff']).'/'.formatStorage($port['stats']['ifOutOctets_diff']).')';
        echo 'pkts('.format_si($port['stats']['ifInUcastPkts_rate']).'pps/'.format_si($port['stats']['ifOutUcastPkts_rate']).'pps)';

        // Port utilisation % threshold alerting. // FIXME allow setting threshold per-port. probably 90% of ports we don't care about.
        if ($config['alerts']['port_util_alert'] && $port['ignore'] == '0') {
            // Check for port saturation of $config['alerts']['port_util_perc'] or higher.  Alert if we see this.
            // Check both inbound and outbound rates
            $saturation_threshold = ($this_port['ifSpeed'] * ( $config['alerts']['port_util_perc'] / 100 ));
            echo 'IN: '.$port['stats']['ifInBits_rate'].' OUT: '.$port['stats']['ifOutBits_rate'].' THRESH: '.$saturation_threshold;
            if (($port['stats']['ifInBits_rate'] >= $saturation_threshold || $port['stats']['ifOutBits_rate'] >= $saturation_threshold) && $saturation_threshold > 0) {
                log_event('Port reached saturation threshold: '.formatRates($port['stats']['ifInBits_rate']).'/'.formatRates($port['stats']['ifOutBits_rate']).' - ifspeed: '.formatRates($this_port['stats']['ifSpeed']), $device, 'interface', $port['port_id']);
            }
        }

        // Update RRDs
        $rrdfile = $host_rrd.'/port-'.safename($port['ifIndex'].'.rrd');
        if (!is_file($rrdfile)) {
            rrdtool_create(
                $rrdfile,
                ' --step 300 
                DS:INOCTETS:DERIVE:600:0:12500000000 
                DS:OUTOCTETS:DERIVE:600:0:12500000000 
                DS:INERRORS:DERIVE:600:0:12500000000 
                DS:OUTERRORS:DERIVE:600:0:12500000000 
                DS:INUCASTPKTS:DERIVE:600:0:12500000000 
                DS:OUTUCASTPKTS:DERIVE:600:0:12500000000 
                DS:INNUCASTPKTS:DERIVE:600:0:12500000000 
                DS:OUTNUCASTPKTS:DERIVE:600:0:12500000000 
                DS:INDISCARDS:DERIVE:600:0:12500000000 
                DS:OUTDISCARDS:DERIVE:600:0:12500000000 
                DS:INUNKNOWNPROTOS:DERIVE:600:0:12500000000 
                DS:INBROADCASTPKTS:DERIVE:600:0:12500000000 
                DS:OUTBROADCASTPKTS:DERIVE:600:0:12500000000 
                DS:INMULTICASTPKTS:DERIVE:600:0:12500000000 
                DS:OUTMULTICASTPKTS:DERIVE:600:0:12500000000 '.$config['rrd_rra']
            );
        }//end if

        $fields = array(
            'INOCTETS'         => $this_port['ifInOctets'],
            'OUTOCTETS'        => $this_port['ifOutOctets'],
            'INERRORS'         => $this_port['ifInErrors'],
            'OUTERRORS'        => $this_port['ifOutErrors'],
            'INUCASTPKTS'      => $this_port['ifInUcastPkts'],
            'OUTUCASTPKTS'     => $this_port['ifOutUcastPkts'],
            'INNUCASTPKTS'     => $this_port['ifInNUcastPkts'],
            'OUTNUCASTPKTS'    => $this_port['ifOutNUcastPkts'],
            'INDISCARDS'       => $this_port['ifInDiscards'],
            'OUTDISCARDS'      => $this_port['ifOutDiscards'],
            'INUNKNOWNPROTOS'  => $this_port['ifInUnknownProtos'],
            'INBROADCASTPKTS'  => $this_port['ifInBroadcastPkts'],
            'OUTBROADCASTPKTS' => $this_port['ifOutBroadcastPkts'],
            'INMULTICASTPKTS'  => $this_port['ifInMulticastPkts'],
            'OUTMULTICASTPKTS' => $this_port['ifOutMulticastPkts'],
        );

        if ($tune_port === true) {
            rrdtool_tune('port',$rrdfile,$this_port['ifSpeed']);
        }
        rrdtool_update("$rrdfile", $fields);

        $fields['ifInUcastPkts_rate'] = $port['ifInUcastPkts_rate'];
        $fields['ifOutUcastPkts_rate'] = $port['ifOutUcastPkts_rate'];
        $fields['ifInErrors_rate'] = $port['ifInErrors_rate'];
        $fields['ifOutErrors_rate'] = $port['ifOutErrors_rate'];
        $fields['ifInOctets_rate'] = $port['ifInOctets_rate'];
        $fields['ifOutOctets_rate'] = $port['ifOutOctets_rate'];

        $tags = array('ifName' => $port['ifName'], 'port_descr_type' => $port['port_descr_type']);
        influx_update($device,'ports',$tags,$fields);

        // End Update IF-MIB
        // Update PAgP
        if ($this_port['pagpOperationMode'] || $port['pagpOperationMode']) {
            foreach ($pagp_oids as $oid) {
                // Loop the OIDs
                if ($this_port[$oid] != $port[$oid]) {
                    // If data has changed, build a query
                    $port['update'][$oid] = $this_port[$oid];
                    echo 'PAgP ';
                    log_event("$oid -> ".$this_port[$oid], $device, 'interface', $port['port_id']);
                }
            }
        }

        // End Update PAgP
        // Do EtherLike-MIB
        if ($config['enable_ports_etherlike']) {
            include 'port-etherlike.inc.php';
        }

        // Do ADSL MIB
        if ($config['enable_ports_adsl']) {
            include 'port-adsl.inc.php';
        }

        // Do PoE MIBs
        if ($config['enable_ports_poe']) {
            include 'port-poe.inc.php';
        }

        // Do Alcatel Detailed Stats
        if ($device['os'] == 'aos') {
            include 'port-alcatel.inc.php';
        }

        foreach ($port['update'] as $key => $val_check) {
            if (!isset($val_check)) {
                unset($port['update'][$key]);
            }
        }

        // Update Database
        if (count($port['update'])) {
            $updated = dbUpdate($port['update'], 'ports', '`port_id` = ?', array($port['port_id']));
            // do we want to do something else with this?
            $updated += dbUpdate($port['update_extended'], 'ports_statistics', '`port_id` = ?', array($port['port_id']));
            d_echo("$updated updated");
        }

        // End Update Database
    }
    else if ($port['disabled'] != '1') {
        echo 'Port Deleted';
        // Port missing from SNMP cache.
        if ($port['deleted'] != '1') {
            dbUpdate(array('deleted' => '1'), 'ports', '`device_id` = ? AND `ifIndex` = ?', array($device['device_id'], $port['ifIndex']));
        }
    }
    else {
        echo 'Port Disabled.';
    }//end if

    echo "\n";

    // Clear Per-Port Variables Here
    unset($this_port);
}//end foreach

// Clear Variables Here
unset($port_stats);
