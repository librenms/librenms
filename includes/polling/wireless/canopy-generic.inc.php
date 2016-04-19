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
    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-errorCount.rrd";
    $fecInErrorsCount = snmp_get($device, "fecInErrorsCount.0", "-Ovqn", "WHISP-BOX-MIBV2-MIB");
    $fecOutErrorsCount = snmp_get($device, "fecOutErrorsCount.0", "-Ovqn", "WHISP-BOX-MIBV2-MIB");
    if (is_numeric($fecInErrorsCount) && is_numeric($fecOutErrorsCount)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:fecInErrorsCount:GAUGE:600:0:100000 DS:fecOutErrorsCount:GAUGE:600:0:100000".$config['rrd_rra']); 
        }
        $fields = array(
            'fecInErrorsCount' => $fecInErrorsCount,
            'fecOutErrorsCount' => $fecOutErrorsCount,
        );
        rrdtool_update($rrd_filename, $fields);
        $graphs['canopy_generic_errorCount'] = TRUE;
        unset($rrd_filename,$fecInErrorsCount,$fecOutErrorsCount);
    }

    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-crcErrors.rrd";
    $crcErrors = snmp_get($device, "fecCRCError.0", "-Ovqn", "WHISP-BOX-MIBV2-MIB");
    if (is_numeric($crcErrors)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:crcErrors:GAUGE:600:0:100000".$config['rrd_rra']); 
        }
        $fields = array(
            'crcErrors' => $crcErrors,
        );
        rrdtool_update($rrd_filename, $fields);
        $graphs['canopy_generic_crcErrors'] = TRUE;
    }

    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-signalHV.rrd";
    $vertical = str_replace('"',"",snmp_get($device, ".1.3.6.1.4.1.161.19.3.2.2.117.0", "-Ovqn", ""));
    $horizontal = str_replace('"',"",snmp_get($device, ".1.3.6.1.4.1.161.19.3.2.2.118.0", "-Ovqn", ""));
    $combined = snmp_get($device, "1.3.6.1.4.1.161.19.3.2.2.21.0", "-Ovqn", "");
    if (is_numeric($vertical) && is_numeric($horizontal) && is_numeric($combined)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:vertical:GAUGE:600:-150:0 DS:horizontal:GAUGE:600:-150:0 DS:combined:GAUGE:600:-150:0".$config['rrd_rra']); 
        }
        $fields = array(
            'vertical' => floatval($vertical),
            'horizontal' => floatval($horizontal),
            'combined' => $combined,
        );
        rrdtool_update($rrd_filename, $fields);
        $graphs['canopy_generic_signalHV'] = TRUE;
        unset($rrd_filename,$vertical,$horizontal,$combined);
    }

    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-rssi.rrd";
    $rssi = snmp_get($device, "1.3.6.1.4.1.161.19.3.2.2.2.0", "-Ovqn", "");
    if (is_numeric($rssi)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:rssi:GAUGE:600:0:5000".$config['rrd_rra']); 
        }
        $fields = array(
            'rssi' => $rssi,
        );
        rrdtool_update($rrd_filename, $fields);
        $graphs['canopy_generic_rssi'] = TRUE;
        unset($rrd_filename,$rssi);
    }

    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-jitter.rrd";
    $jitter = snmp_get($device, "jitter.0", "-Ovqn", "WHISP-SM-MIB");
    if (is_numeric($jitter)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:jitter:GAUGE:600:0:20".$config['rrd_rra']); 
        }
        $fields = array(
            'jitter' => $jitter,
        );
        rrdtool_update($rrd_filename, $fields);
        $graphs['canopy_generic_jitter'] = TRUE;
        unset($rrd_filename,$jitter);
    }

    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-450-slaveHV.rrd";
    $horizontal = str_replace('"',"",snmp_get($device, "radioDbmHorizontal.0", "-Ovqn", "WHISP-SM-MIB"));
    $vertical = str_replace('"',"",snmp_get($device, "radioDbmVertical.0", "-Ovqn", "WHISP-SM-MIB"));
    if (is_numeric($horizontal) && is_numeric($vertical)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:horizontal:GAUGE:600:-100:100 DS:vertical:GAUGE:600:-100:100".$config['rrd_rra']); 
        }
        $fields = array(
            'horizontal' => $horizontal,
            'vertical' => $vertical,
        );
        rrdtool_update($rrd_filename, $fields);
        $graphs['canopy_generic_450_slaveHV'] = TRUE;
        unset($rrd_filename,$horizontal,$vertical);
    }

    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-450-slaveSSR.rrd";
    $ssr = str_replace('"',"",snmp_get($device, "signalStrengthRatio.0", "-Ovqn", "WHISP-SM-MIB"));
    if (is_numeric($ssr)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:ssr:GAUGE:600:-150:150".$config['rrd_rra']); 
        }
        $fields = array(
            'ssr' => $ssr,
        );
        rrdtool_update($rrd_filename, $fields);
        $graphs['canopy_generic_450_slaveSSR'] = TRUE;
        unset($rrd_filename,$ssr);
    }

    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-450-slaveSNR.rrd";
    $horizontal = str_replace('"',"",snmp_get($device, "signalToNoiseRatioSMHorizontal.0", "-Ovqn", "WHISP-SM-MIB"));
    $vertical = str_replace('"',"",snmp_get($device, "signalToNoiseRatioSMVertical.0", "-Ovqn", "WHISP-SM-MIB"));
    if (is_numeric($horizontal) && is_numeric($vertical)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:horizontal:GAUGE:600:0:100 DS:vertical:GAUGE:600:0:100".$config['rrd_rra']); 
        }
        $fields = array(
            'horizontal' => $horizontal,
            'vertical' => $vertical,
        );
        rrdtool_update($rrd_filename, $fields);
        $graphs['canopy_generic_450_slaveSNR'] = TRUE;
        unset($rrd_filename,$horizontal,$vertical);
    }
}

if (strstr($hardware, 'AP') || strstr($hardware, 'Master') || strstr($hardware, 'CMM')) {
    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-whispGPSStats.rrd";
    $gpsStatus = snmp_get($device, "whispGPSStats.0", "-Ovqn", "WHISP-APS-MIB");
    if ($gpsStatus == 'generatingSync') {
        $gpsStatus = 3;
    }
    else if ($gpsStatus == 'gpsLostSync') {
        $gpsStatus = 2;
    }
    else if ($gpsStatus == 'gpsSynchronized') {
        $gpsStatus = 1;
    }
    if (is_numeric($gpsStatus)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:whispGPSStats:GAUGE:600:0:4".$config['rrd_rra']); 
        }
        $fields = array(
            'whispGPSStats' => $gpsStatus,
        );
        rrdtool_update($rrd_filename, $fields);
        $graphs['canopy_generic_whispGPSStats'] = TRUE;
        unset($rrd_filename,$gpsStatus);
    }

    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-gpsStats.rrd";
    $visible = str_replace('"',"",snmp_get($device, ".1.3.6.1.4.1.161.19.3.4.4.7.0", "-Ovqn", ""));
    $tracked = str_replace('"',"",snmp_get($device, ".1.3.6.1.4.1.161.19.3.4.4.8.0", "-Ovqn", ""));
    if (is_numeric($visible) && is_numeric($tracked)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:visible:GAUGE:600:0:1000 DS:tracked:GAUGE:600:0:1000".$config['rrd_rra']); 
        }
        $fields = array(
            'visible' => floatval($visible),
            'tracked' => floatval($tracked),
        );
        rrdtool_update($rrd_filename, $fields);
        $graphs['canopy_generic_gpsStats'] = TRUE;
        unset($rrd_filename,$visible,$tracked);
    }
}
    
if (strstr($version, 'AP') == false) {
    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-450-linkRadioDbm.rrd";
    $horizontal = str_replace('"',"",snmp_get($device, "linkRadioDbmHorizontal.2", "-Ovqn", "WHISP-APS-MIB"));
    $vertical = str_replace('"',"",snmp_get($device, "linkRadioDbmVertical.2", "-Ovqn", "WHISP-APS-MIB"));
    if (is_numeric($horizontal) && is_numeric($vertical)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:horizontal:GAUGE:600:-100:0 DS:vertical:GAUGE:600:-100:0".$config['rrd_rra']); 
        }
        $fields = array(
            'horizontal' => $horizontal,
            'vertical' => $vertical,
        );
        rrdtool_update($rrd_filename, $fields);
        $graphs['canopy_generic_450_linkRadioDbm'] = TRUE;
        unset($rrd_filename,$horizontal,$horizontal);
    }

    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-450-powerlevel.rrd";
    $lastLevel = str_replace('"',"",snmp_get($device, "lastPowerLevel.2", "-Ovqn", "WHISP-APS-MIB"));
    if (is_numeric($lastLevel)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:last:GAUGE:600:-100:0".$config['rrd_rra']); 
        }
        $fields = array(
            'last' => $lastLevel,
        );
        rrdtool_update($rrd_filename, $fields);
        $graphs['canopy_generic_450_powerlevel'] = TRUE;
        unset($lastLevel);
    }
    
    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-450-ptpSNR.rrd";
    $horizontal = str_replace('"',"",snmp_get($device, "signalToNoiseRatioHorizontal.2", "-Ovqn", "WHISP-APS-MIB"));
    $vertical = str_replace('"',"",snmp_get($device, "signalToNoiseRatioVertical.2", "-Ovqn", "WHISP-APS-MIB"));
    if (is_numeric($horizontal) && is_numeric($vertical)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:horizontal:GAUGE:600:0:100 DS:vertical:GAUGE:600:0:100".$config['rrd_rra']); 
        }
        $fields = array(
            'horizontal' => $horizontal,
            'vertical' => $vertical,
        );
        rrdtool_update($rrd_filename, $fields);
        $graphs['canopy_generic_450_ptpSNR'] = TRUE;
        unset($rrd_filename,$horizontal,$horizontal);
    }

    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-450-masterSSR.rrd";
    $ssr = str_replace('"',"",snmp_get($device, "linkSignalStrengthRatio.2", "-Ovqn", "WHISP-APS-MIB"));
    if (is_numeric($ssr)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:ssr:GAUGE:600:-150:150".$config['rrd_rra']); 
        }
    $fields = array(
        'ssr' => $ssr,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['canopy_generic_450_masterSSR'] = TRUE;
    unset($rrd_filename,$ssr);
    }

    if (strstr($hardware, 'PTP 230')) { 
        $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-radioDbm.rrd";
        $dbmRadio = str_replace('"',"",snmp_get($device, "radioDbmInt.0", "-Ovqn", "WHISP-SM-MIB"));
        $minRadio = str_replace('"',"",snmp_get($device, "minRadioDbm.0", "-Ovqn", "WHISP-SM-MIB"));
        $maxRadio = str_replace('"',"",snmp_get($device, "maxRadioDbm.0", "-Ovqn", "WHISP-SM-MIB"));
        $avgRadio = str_replace('"',"",snmp_get($device, "radioDbmAvg.0", "-Ovqn", "WHISP-SM-MIB"));

        if (is_numeric($dbmRadio) && is_numeric($minRadio) && is_numeric($maxRadio) && is_numeric($avgRadio)) {
            if (!is_file($rrd_filename)) {
                rrdtool_create($rrd_filename, " --step 300 DS:dbm:GAUGE:600:-100:0 DS:min:GAUGE:600:-100:0 DS:max:GAUGE:600:-100:0 DS:avg:GAUGE:600:-100:0".$config['rrd_rra']); 
            }
            $fields = array(
                'dbm' => $dbmRadio,
                'min' => $minRadio,
                'max' => $maxRadio,
                'avg' => $avgRadio,
            );
            rrdtool_update($rrd_filename, $fields);
            $graphs['canopy_generic_radioDbm'] = TRUE;
            unset($rrd_filename,$dbmRadio,$minRadio,$maxRadio,$avgRadio);
        }
    }
}

//AP Equipment
if (strstr($version, 'AP')) {
    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-regCount.rrd";
    $registered = str_replace('"',"",snmp_get($device, "regCount.0", "-Ovqn", "WHISP-APS-MIB"));
    $failed = str_replace('"',"",snmp_get($device, "regFailureCount.0", "-Ovqn", "WHISP-APS-MIB"));
    if (is_numeric($registered) && is_numeric($failed)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:regCount:GAUGE:600:0:15000 DS:failed:GAUGE:600:0:15000".$config['rrd_rra']); 
        }
        $fields = array(
            'regCount' => $registered,
            'failed' => $failed,
        );
        rrdtool_update($rrd_filename, $fields);
        $graphs['canopy_generic_regCount'] = TRUE;
        unset($rrd_filename,$registered,$failed);
    }
    
    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/canopy-generic-freq.rrd";
    $freq = str_replace('"',"",snmp_get($device, "currentRadioFreqCarrier.0", "-Ovqn", "WHISP-APS-MIB"));
    if (is_numeric($freq)) {
        if (!is_file($rrd_filename)) {
            rrdtool_create($rrd_filename, " --step 300 DS:freq:GAUGE:600:0:100000".$config['rrd_rra']); 
        }
        if ($freq > 99999) {
            $freq = $freq / 100000;
        }
        else {
            $freq = $freq / 10000;
        }
        $fields = array(
            'freq' => $freq,
        );
        rrdtool_update($rrd_filename, $fields);
        $graphs['canopy_generic_freq'] = TRUE;
        unset($rrd_filename,$freq);
    }
}