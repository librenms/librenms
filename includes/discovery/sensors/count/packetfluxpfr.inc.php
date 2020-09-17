<?php

#PACKETFLUX-GNSS-MIB::gnss1PPSMissingPulses.0.0
$oid = '.1.3.6.1.4.1.32050.3.4.1.1.14.0.0';
$current = (snmp_get($device, $oid, '-Oqv') / 1);
discover_sensor($valid['sensor'], 'count', $device, $oid, 'gnss1PPSMissingPulses',
    'packetfluxpfr', 'Missing Pulses', 1, 1, null, null, null, null, $current);

#PACKETFLUX-GNSS-MIB::gnss1PPSFailedSeconds.0.0
$oid = '.1.3.6.1.4.1.32050.3.4.1.1.21.0.0';
$current = (snmp_get($device, $oid, '-Oqv') / 1);
discover_sensor($valid['sensor'], 'count', $device, $oid, 'gnss1PPSFailedSeconds',
    'packetfluxpfr', 'Pulse Failed Seconds', 1, 1, null, null, null, null, $current);

#PACKETFLUX-GNSS-MIB::gnssSatellitesInView.0.0
$oid = '.1.3.6.1.4.1.32050.3.4.1.1.7.0.0';
$current = (snmp_get($device, $oid, '-Oqv') / 1);
discover_sensor($valid['sensor'], 'count', $device, $oid, 'gnssSatellitesInView',
    'packetfluxpfr', 'Sats. in View', 1, 1, null, null, null, null, $current);

#PACKETFLUX-GNSS-MIB::gnssSatellitesUsed.0.0
$oid = '.1.3.6.1.4.1.32050.3.4.1.1.8.0.0';
$current = (snmp_get($device, $oid, '-Oqv') / 1);
discover_sensor($valid['sensor'], 'count', $device, $oid, 'gnssSatellitesUsed',
    'packetfluxpfr', 'Sats. Used', 1, 1, null, null, null, null, $current);
$graphs['gnssSatellitesUsed'] = true;
