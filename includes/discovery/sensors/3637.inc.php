<?php

if ($device['os'] == 'routeros' || 1) {
    $oids = snmp_walk($device, 'flowMeter', '-OsqnU', 'DOMOTICS-MIB');
    d_echo($oids."\n");

    if ($oids !== false) {
        echo 'DOMOTICS-MIB ';
    	$class = 'domotics';

    	$names = reset(snmpwalk_array_num($device, 'flowMeterDescr'));
    	$idxs = reset(snmpwalk_array_num($device, 'flowMeterIndex'));

	$typeMap = array(
		'TempIn'	=> 'temperature',
		'TempOut'	=> 'temperature',
		'AmbientTemp'	=> 'temperature',
		'Humidity'	=> 'humidity',
		'Flow'		=> 'flow',
	);

        foreach($typeMap as $what => $type) {
    		$labels = reset(snmpwalk_array_num($device, 'flowMeter'.$what.'Label'));
    		$divs = reset(snmpwalk_array_num($device, 'flowMeter'.$what.'Divisor'));
		$oidvals = snmpwalk_array_num($device, 'flowMeter'.$what);
    		$vals = reset($oidvals);
		$baseoid = key($oidvals);

		if ($vals) {
			foreach($idxs as $index => $pubIndex) {
				$oid = $baseoid . '.' . $index;
				if (!$labels[$index]) {
					$labels[$index] = $type;
				};
				$descr = $labels[$index] .' '. $names[$index].'/'.$pubIndex;
				$divisor = $divs[$index];
				$temperature = $vals[$index];

            			discover_sensor($valid['sensor'], $type, $device, $oid, $index, $class, $descr, $divisor ? $divisor : 1, '1', null, null, null, null, $temperature);
			        // discover_sensor($valid['sensor'], 'temperature', $device, $oid, 1, 'extreme-temp', $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $value);

       			}
    		}
	}
	echo ".\n";
     }
}

/*
DOMOTICS-MIB::flowMeterIndex.1 = INTEGER: 1
DOMOTICS-MIB::flowMeterIndex.2 = INTEGER: 2
DOMOTICS-MIB::flowMeterDescr.1 = STRING: flow39
DOMOTICS-MIB::flowMeterDescr.2 = STRING: flow37
DOMOTICS-MIB::flowMeterName.1 = STRING: STM32_ENC28J80_DHCP_FLOW_HUM
DOMOTICS-MIB::flowMeterName.2 = STRING: STM32_ENC28J80_DHCP_FLOW_HUM_PRESS
DOMOTICS-MIB::flowMeterBuildDate.1 = STRING: Nov 22 2015 15:16:02
DOMOTICS-MIB::flowMeterBuildDate.2 = STRING: Dec 23 2015 17:17:26
DOMOTICS-MIB::flowMeterRevision.1 = STRING: $Revision: 2111 $
DOMOTICS-MIB::flowMeterRevision.2 = STRING: $Revision: 2111 $
DOMOTICS-MIB::flowMeterVersion.1 = STRING: flow/1.00
DOMOTICS-MIB::flowMeterVersion.2 = STRING: flow/1.01
DOMOTICS-MIB::flowMeterTempInLabel.1 = STRING: 28A9B228060000A5
DOMOTICS-MIB::flowMeterTempInLabel.2 = STRING: 28FFD37D71150286
DOMOTICS-MIB::flowMeterTempIn.1 = Gauge32: 0
DOMOTICS-MIB::flowMeterTempIn.2 = Gauge32: 2
DOMOTICS-MIB::flowMeterTempInDivisor.1 = INTEGER: 10
DOMOTICS-MIB::flowMeterTempInDivisor.2 = INTEGER: 10
DOMOTICS-MIB::flowMeterTempOutLabel.1 = STRING: 28F9142606000031
DOMOTICS-MIB::flowMeterTempOutLabel.2 = STRING: 28FF0F5A73150240
DOMOTICS-MIB::flowMeterTempOut.1 = Gauge32: 10
DOMOTICS-MIB::flowMeterTempOut.2 = Gauge32: 10
DOMOTICS-MIB::flowMeterTempOutDivisor.1 = INTEGER: 10
DOMOTICS-MIB::flowMeterTempOutDivisor.2 = INTEGER: 10
DOMOTICS-MIB::flowMeterAmbientTemp.1 = Gauge32: 29
DOMOTICS-MIB::flowMeterAmbientTemp.2 = Gauge32: 24
DOMOTICS-MIB::flowMeterAmbientTempDivisor.1 = INTEGER: 1
DOMOTICS-MIB::flowMeterAmbientTempDivisor.2 = INTEGER: 1
DOMOTICS-MIB::flowMeterAmbientHumidity.1 = Gauge32: 33
DOMOTICS-MIB::flowMeterAmbientHumidity.2 = Gauge32: 35
DOMOTICS-MIB::flowMeterAmbientHumidityDivisor.1 = INTEGER: 1
DOMOTICS-MIB::flowMeterAmbientHumidityDivisor.2 = INTEGER: 1
DOMOTICS-MIB::flowMeterFlow.1 = Counter32: 343457
DOMOTICS-MIB::flowMeterFlow.2 = Counter32: 195242
DOMOTICS-MIB::flowMeterErrorReports.1 = Counter32: 0
DOMOTICS-MIB::flowMeterErrorReports.2 = Counter32: 0

Temperatures: DOMOTICS-MIB .1.3.6.1.4.1.2692.3739.2.2.1.1.1 1
U.1.3.6.1.4.1.2692.3739.2.2.1.1.2 2
U.1.3.6.1.4.1.2692.3739.2.2.1.2.1 flow39
U.1.3.6.1.4.1.2692.3739.2.2.1.2.2 flow37
U.1.3.6.1.4.1.2692.3739.2.2.1.3.1 STM32_ENC28J80_DHCP_FLOW_HUM
U.1.3.6.1.4.1.2692.3739.2.2.1.3.2 STM32_ENC28J80_DHCP_FLOW_HUM_PRESS
U.1.3.6.1.4.1.2692.3739.2.2.1.4.1 Nov 22 2015 15:16:02
U.1.3.6.1.4.1.2692.3739.2.2.1.4.2 Dec 23 2015 17:17:26
U.1.3.6.1.4.1.2692.3739.2.2.1.5.1 $Revision: 2111 $
U.1.3.6.1.4.1.2692.3739.2.2.1.5.2 $Revision: 2111 $
U.1.3.6.1.4.1.2692.3739.2.2.1.6.1 flow/1.00
U.1.3.6.1.4.1.2692.3739.2.2.1.6.2 flow/1.01
U.1.3.6.1.4.1.2692.3739.2.2.1.7.1 28A9B228060000A5
U.1.3.6.1.4.1.2692.3739.2.2.1.7.2 28FFD37D71150286
U.1.3.6.1.4.1.2692.3739.2.2.1.8.1 2
U.1.3.6.1.4.1.2692.3739.2.2.1.8.2 2
U.1.3.6.1.4.1.2692.3739.2.2.1.9.1 10
U.1.3.6.1.4.1.2692.3739.2.2.1.9.2 10
U.1.3.6.1.4.1.2692.3739.2.2.1.10.1 28F9142606000031
U.1.3.6.1.4.1.2692.3739.2.2.1.10.2 28FF0F5A73150240
U.1.3.6.1.4.1.2692.3739.2.2.1.11.1 10
U.1.3.6.1.4.1.2692.3739.2.2.1.11.2 10
U.1.3.6.1.4.1.2692.3739.2.2.1.12.1 10
U.1.3.6.1.4.1.2692.3739.2.2.1.12.2 10
U.1.3.6.1.4.1.2692.3739.2.2.1.13.1 29
U.1.3.6.1.4.1.2692.3739.2.2.1.13.2 24
U.1.3.6.1.4.1.2692.3739.2.2.1.14.1 1
U.1.3.6.1.4.1.2692.3739.2.2.1.14.2 1
U.1.3.6.1.4.1.2692.3739.2.2.1.15.1 33
U.1.3.6.1.4.1.2692.3739.2.2.1.15.2 35
U.1.3.6.1.4.1.2692.3739.2.2.1.16.1 1
U.1.3.6.1.4.1.2692.3739.2.2.1.16.2 1
U.1.3.6.1.4.1.2692.3739.2.2.1.17.1 349501
U.1.3.6.1.4.1.2692.3739.2.2.1.17.2 198796
U.1.3.6.1.4.1.2692.3739.2.2.1.18.1 0
U.1.3.6.1.4.1.2692.3739.2.2.1.18.2 0
UHP_ILO 


*/
