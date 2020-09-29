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
use LibreNMS\RRD\RrdDefinition;

if (strstr($hardware, 'CMM') == false) {
    $fecInErrorsCount = snmp_get($device, 'fecInErrorsCount.0', '-Ovqn', 'WHISP-BOX-MIBV2-MIB');
    $fecOutErrorsCount = snmp_get($device, 'fecOutErrorsCount.0', '-Ovqn', 'WHISP-BOX-MIBV2-MIB');
    if (is_numeric($fecInErrorsCount) && is_numeric($fecOutErrorsCount)) {
        $rrd_def = RrdDefinition::make()
            ->addDataset('fecInErrorsCount', 'GAUGE', 0, 100000)
            ->addDataset('fecOutErrorsCount', 'GAUGE', 0, 100000);

        $fields = [
            'fecInErrorsCount' => $fecInErrorsCount,
            'fecOutErrorsCount' => $fecOutErrorsCount,
        ];
        $tags = compact('rrd_def');
        data_update($device, 'canopy-generic-errorCount', $tags, $fields);
        $os->enableGraph('canopy_generic_errorCount');
        unset($rrd_filename, $fecInErrorsCount, $fecOutErrorsCount);
    }

    $crcErrors = snmp_get($device, 'fecCRCError.0', '-Ovqn', 'WHISP-BOX-MIBV2-MIB');
    if (is_numeric($crcErrors)) {
        $rrd_def = RrdDefinition::make()->addDataset('crcErrors', 'GAUGE', 0, 100000);
        $fields = [
            'crcErrors' => $crcErrors,
        ];
        $tags = compact('rrd_def');
        data_update($device, 'canopy-generic-crcErrors', $tags, $fields);
        $os->enableGraph('canopy_generic_crcErrors');
    }

    $vertical = str_replace('"', '', snmp_get($device, '.1.3.6.1.4.1.161.19.3.2.2.117.0', '-Ovqn', ''));
    $horizontal = str_replace('"', '', snmp_get($device, '.1.3.6.1.4.1.161.19.3.2.2.118.0', '-Ovqn', ''));
    $combined = snmp_get($device, '1.3.6.1.4.1.161.19.3.2.2.21.0', '-Ovqn', '');
    if (is_numeric($vertical) && is_numeric($horizontal) && is_numeric($combined)) {
        $rrd_def = RrdDefinition::make()
            ->addDataset('vertical', 'GAUGE', -150, 0)
            ->addDataset('horizontal', 'GAUGE', -150, 0)
            ->addDataset('combined', 'GAUGE', -150, 0);
        $fields = [
            'vertical' => floatval($vertical),
            'horizontal' => floatval($horizontal),
            'combined' => $combined,
        ];
        $tags = compact('rrd_def');
        data_update($device, 'canopy-generic-signalHV', $tags, $fields);
        $os->enableGraph('canopy_generic_signalHV');
        unset($rrd_filename, $vertical, $horizontal, $combined);
    }

    // Implemented
    // $rssi = snmp_get($device, "1.3.6.1.4.1.161.19.3.2.2.2.0", "-Ovqn", "");
    // if (is_numeric($rssi)) {
    //     $rrd_def = RrdDefinition::make()->addDataset('rssi', 'GAUGE', 0, 5000);
    //     $fields = array(
    //         'rssi' => $rssi,
    //     );
    //     $tags = compact('rrd_def');
    //     data_update($device, 'canopy-generic-rssi', $tags, $fields);
    //     $os->enableGraph('canopy_generic_rssi');
    //     unset($rrd_filename, $rssi);
    // }

    $jitter = snmp_get($device, 'jitter.0', '-Ovqn', 'WHISP-SM-MIB');
    if (is_numeric($jitter)) {
        $rrd_def = RrdDefinition::make()->addDataset('jitter', 'GAUGE', 0, 20);
        $fields = [
            'jitter' => $jitter,
        ];
        $tags = compact('rrd_def');
        data_update($device, 'canopy-generic-jitter', $tags, $fields);
        $os->enableGraph('canopy_generic_jitter');
        unset($rrd_filename, $jitter);
    }

    $horizontal = str_replace('"', '', snmp_get($device, 'radioDbmHorizontal.0', '-Ovqn', 'WHISP-SM-MIB'));
    $vertical = str_replace('"', '', snmp_get($device, 'radioDbmVertical.0', '-Ovqn', 'WHISP-SM-MIB'));
    if (is_numeric($horizontal) && is_numeric($vertical)) {
        $rrd_def = RrdDefinition::make()
            ->addDataset('horizontal', 'GAUGE', -100, 100)
            ->addDataset('vertical', 'GAUGE', -100, 100);

        $fields = [
            'horizontal' => $horizontal,
            'vertical' => $vertical,
        ];
        $tags = compact('rrd_def');
        data_update($device, 'canopy-generic-450-slaveHV', $tags, $fields);
        $os->enableGraph('canopy_generic_450_slaveHV');
        unset($rrd_filename, $horizontal, $vertical);
    }

    $ssr = str_replace('"', '', snmp_get($device, 'signalStrengthRatio.0', '-Ovqn', 'WHISP-SM-MIB'));
    if (is_numeric($ssr)) {
        $rrd_def = RrdDefinition::make()->addDataset('ssr', 'GAUGE', -150, 150);
        $fields = [
            'ssr' => $ssr,
        ];
        $tags = compact('rrd_def');
        data_update($device, 'canopy-generic-450-slaveSSR', $tags, $fields);
        $os->enableGraph('canopy_generic_450_slaveSSR');
        unset($rrd_filename, $ssr);
    }

    $horizontal = str_replace('"', '', snmp_get($device, 'signalToNoiseRatioSMHorizontal.0', '-Ovqn', 'WHISP-SM-MIB'));
    $vertical = str_replace('"', '', snmp_get($device, 'signalToNoiseRatioSMVertical.0', '-Ovqn', 'WHISP-SM-MIB'));
    if (is_numeric($horizontal) && is_numeric($vertical)) {
        $rrd_def = RrdDefinition::make()
            ->addDataset('horizontal', 'GAUGE', 0, 100)
            ->addDataset('vertical', 'GAUGE', 0, 100);
        $fields = [
            'horizontal' => $horizontal,
            'vertical' => $vertical,
        ];
        $tags = compact('rrd_def');
        data_update($device, 'canopy-generic-450-slaveSNR', $tags, $fields);
        $os->enableGraph('canopy_generic_450_slaveSNR');
        unset($rrd_filename, $horizontal, $vertical);
    }
}
// Convert to: https://docs.librenms.org/#Developing/Sensor-State-Support/
if (strstr($hardware, 'AP') || strstr($hardware, 'Master') || strstr($hardware, 'CMM')) {
    // Implemented
    // $gpsStatus = snmp_get($device, "whispGPSStats.0", "-Ovqn", "WHISP-APS-MIB");
    // if ($gpsStatus == 'generatingSync') {
    //     $gpsStatus = 3;
    // } elseif ($gpsStatus == 'gpsLostSync') {
    //     $gpsStatus = 2;
    // } elseif ($gpsStatus == 'gpsSynchronized') {
    //     $gpsStatus = 1;
    // }
    // if (is_numeric($gpsStatus)) {
    //     $rrd_def = RrdDefinition::make()->addDataset('whispGPSStats', 'GAUGE', 0, 4);
    //     $fields = array(
    //         'whispGPSStats' => $gpsStatus,
    //     );
    //     $tags = compact('rrd_def');
    //     data_update($device, 'canopy-generic-whispGPSStats', $tags, $fields);
    //     $os->enableGraph('canopy_generic_whispGPSStats');
    //     unset($rrd_filename, $gpsStatus);
    // }

    $visible = str_replace('"', '', snmp_get($device, '.1.3.6.1.4.1.161.19.3.4.4.7.0', '-Ovqn', ''));
    $tracked = str_replace('"', '', snmp_get($device, '.1.3.6.1.4.1.161.19.3.4.4.8.0', '-Ovqn', ''));
    if (is_numeric($visible) && is_numeric($tracked)) {
        $rrd_def = RrdDefinition::make()
            ->addDataset('visible', 'GAUGE', 0, 1000)
            ->addDataset('tracked', 'GAUGE', 0, 1000);
        $fields = [
            'visible' => floatval($visible),
            'tracked' => floatval($tracked),
        ];
        $tags = compact('rrd_def');
        data_update($device, 'canopy-generic-gpsStats', $tags, $fields);
        $os->enableGraph('canopy_generic_gpsStats');
        unset($rrd_filename, $visible, $tracked);
    }
}

if (strstr($version, 'AP') == false) {
    $horizontal = str_replace('"', '', snmp_get($device, 'linkRadioDbmHorizontal.2', '-Ovqn', 'WHISP-APS-MIB'));
    $vertical = str_replace('"', '', snmp_get($device, 'linkRadioDbmVertical.2', '-Ovqn', 'WHISP-APS-MIB'));
    if (is_numeric($horizontal) && is_numeric($vertical)) {
        $rrd_def = RrdDefinition::make()
            ->addDataset('horizontal', 'GAUGE', -100, 0)
            ->addDataset('vertical', 'GAUGE', -100, 0);
        $fields = [
            'horizontal' => $horizontal,
            'vertical' => $vertical,
        ];
        $tags = compact('rrd_def');
        data_update($device, 'canopy-generic-450-linkRadioDbm', $tags, $fields);
        $os->enableGraph('canopy_generic_450_linkRadioDbm');
        unset($rrd_filename, $horizontal, $horizontal);
    }

    $lastLevel = str_replace('"', '', snmp_get($device, 'lastPowerLevel.2', '-Ovqn', 'WHISP-APS-MIB'));
    if (is_numeric($lastLevel)) {
        $rrd_def = RrdDefinition::make()->addDataset('last', 'GAUGE', -100, 0);
        $fields = [
            'last' => $lastLevel,
        ];
        $tags = compact('rrd_def');
        data_update($device, 'canopy-generic-450-powerlevel', $tags, $fields);
        $os->enableGraph('canopy_generic_450_powerlevel');
        unset($lastLevel);
    }

    // Implemented
    // $horizontal = str_replace('"', "", snmp_get($device, "signalToNoiseRatioHorizontal.2", "-Ovqn", "WHISP-APS-MIB"));
    // $vertical = str_replace('"', "", snmp_get($device, "signalToNoiseRatioVertical.2", "-Ovqn", "WHISP-APS-MIB"));
    // if (is_numeric($horizontal) && is_numeric($vertical)) {
    //     $rrd_def = RrdDefinition::m    //         ->addDataset('horizontal', 'GAUGE', 0, 100)ake()

    //         ->addDataset('vertical', 'GAUGE', 0, 100);
    //     $fields = array(
    //         'horizontal' => $horizontal,
    //         'vertical' => $vertical,
    //     );
    //     $tags = compact('rrd_def');
    //     data_update($device, 'canopy-generic-450-ptpSNR', $tags, $fields);
    //     $os->enableGraph('canopy_generic_450_ptpSNR');
    //     unset($rrd_filename, $horizontal, $horizontal);
    // }

    $ssr = str_replace('"', '', snmp_get($device, 'linkSignalStrengthRatio.2', '-Ovqn', 'WHISP-APS-MIB'));
    if (is_numeric($ssr)) {
        $rrd_def = RrdDefinition::make()->addDataset('ssr', 'GAUGE', -150, 150);
        $fields = [
            'ssr' => $ssr,
        ];
        $tags = compact('rrd_def');
        data_update($device, 'canopy-generic-450-masterSSR', $tags, $fields);
        $os->enableGraph('canopy_generic_450_masterSSR');
        unset($rrd_filename, $ssr);
    }

    if (strstr($hardware, 'PTP 230')) {
        $dbmRadio = str_replace('"', '', snmp_get($device, 'radioDbmInt.0', '-Ovqn', 'WHISP-SM-MIB'));
        $minRadio = str_replace('"', '', snmp_get($device, 'minRadioDbm.0', '-Ovqn', 'WHISP-SM-MIB'));
        $maxRadio = str_replace('"', '', snmp_get($device, 'maxRadioDbm.0', '-Ovqn', 'WHISP-SM-MIB'));
        $avgRadio = str_replace('"', '', snmp_get($device, 'radioDbmAvg.0', '-Ovqn', 'WHISP-SM-MIB'));

        if (is_numeric($dbmRadio) && is_numeric($minRadio) && is_numeric($maxRadio) && is_numeric($avgRadio)) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('dbm', 'GAUGE', -100, 0)
                ->addDataset('min', 'GAUGE', -100, 0)
                ->addDataset('max', 'GAUGE', -100, 0)
                ->addDataset('avg', 'GAUGE', -100, 0);

            $fields = [
                'dbm' => $dbmRadio,
                'min' => $minRadio,
                'max' => $maxRadio,
                'avg' => $avgRadio,
            ];
            $tags = compact('rrd_def');
            data_update($device, 'canopy-generic-radioDbm', $tags, $fields);
            $os->enableGraph('canopy_generic_radioDbm');
            unset($rrd_filename, $dbmRadio, $minRadio, $maxRadio, $avgRadio);
        }
    }
}

//AP Equipment
if (strstr($version, 'AP')) {
    $multi_get_array = snmp_get_multi($device, ['regCount.0', 'regFailureCount.0', 'currentRadioFreqCarrier.0', 'frUtlLowTotalDownlinkUtilization.0', 'frUtlLowTotalUplinkUtilization.0'], '-OQU', 'WHISP-APS-MIB');
    d_echo($multi_get_array);
    $registered = $multi_get_array[0]['WHISP-APS-MIB::regCount'];
    $failed = $multi_get_array[0]['WHISP-APS-MIB::regFailureCount'];
    $freq = $multi_get_array[0]['WHISP-APS-MIB::currentRadioFreqCarrier'];
    $downlinkutilization = $multi_get_array[0]['WHISP-APS-MIB::frUtlLowTotalDownlinkUtilization'];
    $uplinkutilization = $multi_get_array[0]['WHISP-APS-MIB::frUtlLowTotalUplinkUtilization'];

    if (is_numeric($registered) && is_numeric($failed)) {
        $rrd_def = RrdDefinition::make()
            ->addDataset('regCount', 'GAUGE', 0, 15000)
            ->addDataset('failed', 'GAUGE', 0, 15000);
        $fields = [
            'regCount' => $registered,
            'failed' => $failed,
        ];
        $tags = compact('rrd_def');
        data_update($device, 'canopy-generic-regCount', $tags, $fields);
        $os->enableGraph('canopy_generic_regCount');
        unset($rrd_filename, $registered, $failed);
    }

    // Implemented
    // if (is_numeric($freq)) {
    //     $rrd_def = RrdDefinition::make()->addDataset('freq', 'GAUGE', 0, 100000);
    //     if ($freq > 99999) {
    //         $freq = $freq / 100000;
    //     } else {
    //         $freq = $freq / 10000;
    //     }
    //     $fields = array(
    //         'freq' => $freq,
    //     );
    //     $tags = compact('rrd_def');
    //     data_update($device, 'canopy-generic-freq', $tags, $fields);
    //     $os->enableGraph('canopy_generic_freq');
    //     unset($rrd_filename, $freq);
    // }

    // implemented
    // if (is_numeric($downlinkutilization) && is_numeric($uplinkutilization)) {
    //     $rrd_def = RrdDefinition::make()
    //         ->addDataset('downlinkutilization', 'GAUGE', 0, 15000)
    //         ->addDataset('uplinkutilization', 'GAUGE', 0, 15000);
    //     $fields = array(
    //         'downlinkutilization' => $downlinkutilization,
    //         'uplinkutilization' => $uplinkutilization,
    //     );
    //     $tags = compact('rrd_def');
    //     data_update($device, 'canopy-generic-frameUtilization', $tags, $fields);
    //     $os->enableGraph('canopy-generic-frameUtilization');
    //     unset($rrd_filename, $downlinkutilization, $uplinkutilization);
    // }
}
