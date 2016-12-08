<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
if (strstr($hardware, 'CMM') == false) {
    $fecInErrorsCount = snmp_get($device, "fecInErrorsCount.0", "-Ovqn", "WHISP-BOX-MIBV2-MIB");
    $fecOutErrorsCount = snmp_get($device, "fecOutErrorsCount.0", "-Ovqn", "WHISP-BOX-MIBV2-MIB");
    if (is_numeric($fecInErrorsCount) && is_numeric($fecOutErrorsCount)) {
        $rrd_def = array(
            'DS:fecInErrorsCount:GAUGE:600:0:100000',
            'DS:fecOutErrorsCount:GAUGE:600:0:100000'
        );
        $fields = array(
            'fecInErrorsCount' => $fecInErrorsCount,
            'fecOutErrorsCount' => $fecOutErrorsCount,
        );
        $tags = compact('rrd_def');
        data_update($device, 'cambium-generic-errorCount', $tags, $fields);
        $graphs['cambium_generic_errorCount'] = true;
        unset($rrd_filename,$fecInErrorsCount,$fecOutErrorsCount);
    }

    $crcErrors = snmp_get($device, "fecCRCError.0", "-Ovqn", "WHISP-BOX-MIBV2-MIB");
    if (is_numeric($crcErrors)) {
        $rrd_def = 'DS:crcErrors:GAUGE:600:0:100000';
        $fields = array(
            'crcErrors' => $crcErrors,
        );
        $tags = compact('rrd_def');
        data_update($device, 'cambium-generic-crcErrors', $tags, $fields);
        $graphs['cambium_generic_crcErrors'] = true;
    }

    $vertical = str_replace('"', "", snmp_get($device, ".1.3.6.1.4.1.161.19.3.2.2.117.0", "-Ovqn", ""));
    $horizontal = str_replace('"', "", snmp_get($device, ".1.3.6.1.4.1.161.19.3.2.2.118.0", "-Ovqn", ""));
    $combined = snmp_get($device, "1.3.6.1.4.1.161.19.3.2.2.21.0", "-Ovqn", "");
    if (is_numeric($vertical) && is_numeric($horizontal) && is_numeric($combined)) {
        $rrd_def = array(
            'DS:vertical:GAUGE:600:-150:0',
            'DS:horizontal:GAUGE:600:-150:0',
            'DS:combined:GAUGE:600:-150:0'
        );
        $fields = array(
            'vertical' => floatval($vertical),
            'horizontal' => floatval($horizontal),
            'combined' => $combined,
        );
        $tags = compact('rrd_def');
        data_update($device, 'cambium-generic-signalHV', $tags, $fields);
        $graphs['cambium_generic_signalHV'] = true;
        unset($rrd_filename,$vertical,$horizontal,$combined);
    }

    $rssi = snmp_get($device, "1.3.6.1.4.1.161.19.3.2.2.2.0", "-Ovqn", "");
    if (is_numeric($rssi)) {
        $rrd_def = 'DS:rssi:GAUGE:600:0:5000';
        $fields = array(
            'rssi' => $rssi,
        );
        $tags = compact('rrd_def');
        data_update($device, 'cambium-generic-rssi', $tags, $fields);
        $graphs['cambium_generic_rssi'] = true;
        unset($rrd_filename,$rssi);
    }

    $jitter = snmp_get($device, "jitter.0", "-Ovqn", "WHISP-SM-MIB");
    if (is_numeric($jitter)) {
        $rrd_def = 'DS:jitter:GAUGE:600:0:20';
        $fields = array(
            'jitter' => $jitter,
        );
        $tags = compact('rrd_def');
        data_update($device, 'cambium-generic-jitter', $tags, $fields);
        $graphs['cambium_generic_jitter'] = true;
        unset($rrd_filename,$jitter);
    }

    $horizontal = str_replace('"', "", snmp_get($device, "radioDbmHorizontal.0", "-Ovqn", "WHISP-SM-MIB"));
    $vertical = str_replace('"', "", snmp_get($device, "radioDbmVertical.0", "-Ovqn", "WHISP-SM-MIB"));
    if (is_numeric($horizontal) && is_numeric($vertical)) {
        $rrd_def = array(
            'DS:horizontal:GAUGE:600:-100:100',
            'DS:vertical:GAUGE:600:-100:100'
        );
        $fields = array(
            'horizontal' => $horizontal,
            'vertical' => $vertical,
        );
        $tags = compact('rrd_def');
        data_update($device, 'cambium-generic-450-slaveHV', $tags, $fields);
        $graphs['cambium_generic_450_slaveHV'] = true;
        unset($rrd_filename,$horizontal,$vertical);
    }

    $ssr = str_replace('"', "", snmp_get($device, "signalStrengthRatio.0", "-Ovqn", "WHISP-SM-MIB"));
    if (is_numeric($ssr)) {
        $rrd_def = 'DS:ssr:GAUGE:600:-150:150';
        $fields = array(
            'ssr' => $ssr,
        );
        $tags = compact('rrd_def');
        data_update($device, 'cambium-generic-450-slaveSSR', $tags, $fields);
        $graphs['cambium_generic_450_slaveSSR'] = true;
        unset($rrd_filename,$ssr);
    }

    $horizontal = str_replace('"', "", snmp_get($device, "signalToNoiseRatioSMHorizontal.0", "-Ovqn", "WHISP-SM-MIB"));
    $vertical = str_replace('"', "", snmp_get($device, "signalToNoiseRatioSMVertical.0", "-Ovqn", "WHISP-SM-MIB"));
    if (is_numeric($horizontal) && is_numeric($vertical)) {
        $rrd_def = array(
            'DS:horizontal:GAUGE:600:0:100',
            'DS:vertical:GAUGE:600:0:100'
        );
        $fields = array(
            'horizontal' => $horizontal,
            'vertical' => $vertical,
        );
        $tags = compact('rrd_def');
        data_update($device, 'cambium-generic-450-slaveSNR', $tags, $fields);
        $graphs['cambium_generic_450_slaveSNR'] = true;
        unset($rrd_filename,$horizontal,$vertical);
    }
}

if (strstr($hardware, 'AP') || strstr($hardware, 'Master') || strstr($hardware, 'CMM')) {
    $gpsStatus = snmp_get($device, "whispGPSStats.0", "-Ovqn", "WHISP-APS-MIB");
    if ($gpsStatus == 'generatingSync') {
        $gpsStatus = 3;
    } elseif ($gpsStatus == 'gpsLostSync') {
        $gpsStatus = 2;
    } elseif ($gpsStatus == 'gpsSynchronized') {
        $gpsStatus = 1;
    }
    if (is_numeric($gpsStatus)) {
        $rrd_def = 'DS:whispGPSStats:GAUGE:600:0:4';
        $fields = array(
            'whispGPSStats' => $gpsStatus,
        );
        $tags = compact('rrd_def');
        data_update($device, 'cambium-generic-whispGPSStats', $tags, $fields);
        $graphs['cambium_generic_whispGPSStats'] = true;
        unset($rrd_filename,$gpsStatus);
    }

    $visible = str_replace('"', "", snmp_get($device, ".1.3.6.1.4.1.161.19.3.4.4.7.0", "-Ovqn", ""));
    $tracked = str_replace('"', "", snmp_get($device, ".1.3.6.1.4.1.161.19.3.4.4.8.0", "-Ovqn", ""));
    if (is_numeric($visible) && is_numeric($tracked)) {
        $rrd_def = array(
            'DS:visible:GAUGE:600:0:1000',
            'DS:tracked:GAUGE:600:0:1000'
        );
        $fields = array(
            'visible' => floatval($visible),
            'tracked' => floatval($tracked),
        );
        $tags = compact('rrd_def');
        data_update($device, 'cambium-generic-gpsStats', $tags, $fields);
        $graphs['cambium_generic_gpsStats'] = true;
        unset($rrd_filename,$visible,$tracked);
    }
}
//PTP Equipment
    $lastLevel = str_replace('"', "", snmp_get($device, "lastPowerLevel.2", "-Ovqn", "WHISP-APS-MIB"));
if (is_numeric($lastLevel)) {
    $rrd_def = 'DS:last:GAUGE:600:-100:0';
    $fields = array(
    'last' => $lastLevel,
    );
    $tags = compact('rrd_def');
    data_update($device, 'cambium-generic-450-powerlevel', $tags, $fields);
    $graphs['cambium_generic_450_powerlevel'] = true;
    unset($lastLevel);
}
    
if (strstr($version, 'AP') == false) {
    $horizontal = str_replace('"', "", snmp_get($device, "linkRadioDbmHorizontal.2", "-Ovqn", "WHISP-APS-MIB"));
    $vertical = str_replace('"', "", snmp_get($device, "linkRadioDbmVertical.2", "-Ovqn", "WHISP-APS-MIB"));
    if (is_numeric($horizontal) && is_numeric($vertical)) {
        $rrd_def = array(
            'DS:horizontal:GAUGE:600:-100:0',
            'DS:vertical:GAUGE:600:-100:0'
        );
        $fields = array(
            'horizontal' => $horizontal,
            'vertical' => $vertical,
        );
        $tags = compact('rrd_def');
        data_update($device, 'cambium-generic-450-linkRadioDbm', $tags, $fields);
        $graphs['cambium_generic_450_linkRadioDbm'] = true;
        unset($rrd_filename,$horizontal,$horizontal);
    }

    $horizontal = str_replace('"', "", snmp_get($device, "signalToNoiseRatioHorizontal.2", "-Ovqn", "WHISP-APS-MIB"));
    $vertical = str_replace('"', "", snmp_get($device, "signalToNoiseRatioVertical.2", "-Ovqn", "WHISP-APS-MIB"));
    if (is_numeric($horizontal) && is_numeric($vertical)) {
        $rrd_def = array(
            'DS:horizontal:GAUGE:600:0:100',
            'DS:vertical:GAUGE:600:0:100'
        );
        $fields = array(
            'horizontal' => $horizontal,
            'vertical' => $vertical,
        );
        $tags = compact('rrd_def');
        data_update($device, 'cambium-generic-450-ptpSNR', $tags, $fields);
        $graphs['cambium_generic_450_ptpSNR'] = true;
        unset($rrd_filename,$horizontal,$horizontal);
    }

    $ssr = str_replace('"', "", snmp_get($device, "linkSignalStrengthRatio.2", "-Ovqn", "WHISP-APS-MIB"));
    if (is_numeric($ssr)) {
        $rrd_def = 'DS:ssr:GAUGE:600:-150:150';
        $fields = array(
        'ssr' => $ssr,
        );
        $tags = compact('rrd_def');
        data_update($device, 'cambium-generic-450-masterSSR', $tags, $fields);
        $graphs['cambium_generic_450_masterSSR'] = true;
        unset($rrd_filename,$ssr);
    }

    if (strstr($hardware, 'PTP 230')) {
        $dbmRadio = str_replace('"', "", snmp_get($device, "radioDbmInt.0", "-Ovqn", "WHISP-SM-MIB"));
        $minRadio = str_replace('"', "", snmp_get($device, "minRadioDbm.0", "-Ovqn", "WHISP-SM-MIB"));
        $maxRadio = str_replace('"', "", snmp_get($device, "maxRadioDbm.0", "-Ovqn", "WHISP-SM-MIB"));
        $avgRadio = str_replace('"', "", snmp_get($device, "radioDbmAvg.0", "-Ovqn", "WHISP-SM-MIB"));

        if (is_numeric($dbmRadio) && is_numeric($minRadio) && is_numeric($maxRadio) && is_numeric($avgRadio)) {
            $rrd_def = array(
                'DS:dbm:GAUGE:600:-100:0',
                'DS:min:GAUGE:600:-100:0',
                'DS:max:GAUGE:600:-100:0',
                'DS:avg:GAUGE:600:-100:0'
            );
            $fields = array(
                'dbm' => $dbmRadio,
                'min' => $minRadio,
                'max' => $maxRadio,
                'avg' => $avgRadio,
            );
            $tags = compact('rrd_def');
            data_update($device, 'cambium-generic-radioDbm', $tags, $fields);
            $graphs['cambium_generic_radioDbm'] = true;
            unset($rrd_filename,$dbmRadio,$minRadio,$maxRadio,$avgRadio);
        }
    }
}

//AP Equipment
if (strstr($version, 'AP')) {
    $registered = str_replace('"', "", snmp_get($device, "regCount.0", "-Ovqn", "WHISP-APS-MIB"));
    $failed = str_replace('"', "", snmp_get($device, "regFailureCount.0", "-Ovqn", "WHISP-APS-MIB"));
    if (is_numeric($registered) && is_numeric($failed)) {
        $rrd_def = array(
            'DS:regCount:GAUGE:600:0:15000',
            'DS:failed:GAUGE:600:0:15000'
        );
        $fields = array(
            'regCount' => $registered,
            'failed' => $failed,
        );
        $tags = compact('rrd_def');
        data_update($device, 'cambium-generic-regCount', $tags, $fields);
        $graphs['cambium_generic_regCount'] = true;
        unset($rrd_filename,$registered,$failed);
    }
    
    $freq = str_replace('"', "", snmp_get($device, "currentRadioFreqCarrier.0", "-Ovqn", "WHISP-APS-MIB"));
    if (is_numeric($freq)) {
        $rrd_def = 'DS:freq:GAUGE:600:0:100000';
        if ($freq > 99999) {
            $freq = $freq / 100000;
        } else {
            $freq = $freq / 10000;
        }
        $fields = array(
            'freq' => $freq,
        );
        $tags = compact('rrd_def');
        data_update($device, 'cambium-generic-freq', $tags, $fields);
        $graphs['cambium_generic_freq'] = true;
        unset($rrd_filename,$freq);
    }
}
