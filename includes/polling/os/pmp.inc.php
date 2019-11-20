<?php
/*
 * LibreNMS
 *
 * pmp.inc.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Paul Heinrichs
 * @author     Paul Heinrichs<pdheinrichs@gmail.com>
 */
 use LibreNMS\RRD\RrdDefinition;

$cambium_type = $device['sysDescr'];
$PMP = snmp_get($device, 'boxDeviceType.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
$version = $cambium_type;

$filtered_words = array(
    'timing',
    'timeing'
);

$ptp = array(
    'BHUL450'       => 'PTP 450',
    'BHUL'          => 'PTP 230',
    'BH20'          => 'PTP 100'
);

// PMP 100 is defaulted to
$pmp = array(
    'MU-MIMO OFDM'  => 'PMP 450m',
    'MIMO OFDM'     => 'PMP 450',
    'OFDM'          => 'PMP 430'
);

foreach ($ptp as $desc => $model) {
    if (str_contains($cambium_type, $desc)) {
        $hardware = $model;

        if (str_contains($model, 'PTP')) {
            $masterSlaveMode = str_replace($filtered_words, "", snmp_get($device, 'bhTimingMode.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB'));
            $hardware = $model . ' '. $masterSlaveMode;
            $version = snmp_get($device, 'boxDeviceTypeID.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
        }
        break;
    }
}

if (!isset($hardware)) {
    $hardware = 'PMP 100';
    foreach ($pmp as $desc => $model) {
        if (str_contains($PMP, $desc)) {
            $hardware = $model;
            break;
        }
    }
    if (str_contains($hardware, 'PMP')) {
        if (str_contains($version, "AP")) {
            $hardware .= ' AP';
        } elseif (str_contains($version, "SM")) {
            $hardware .= ' SM';
        }
    }
}

// Migrated to Wireless Sensor
$fecInErrorsCount = snmp_get($device, "fecInErrorsCount.0", "-Ovqn", "WHISP-BOX-MIBV2-MIB");
$fecOutErrorsCount = snmp_get($device, "fecOutErrorsCount.0", "-Ovqn", "WHISP-BOX-MIBV2-MIB");
if (is_numeric($fecInErrorsCount) && is_numeric($fecOutErrorsCount)) {
    $rrd_def = RrdDefinition::make()
        ->addDataset('fecInErrorsCount', 'GAUGE', 0, 100000)
        ->addDataset('fecOutErrorsCount', 'GAUGE', 0, 100000);

    $fields = array(
        'fecInErrorsCount' => $fecInErrorsCount,
        'fecOutErrorsCount' => $fecOutErrorsCount,
    );
    $tags = compact('rrd_def');
    data_update($device, 'canopy-generic-errorCount', $tags, $fields);
    $graphs['canopy_generic_errorCount'] = true;
    unset($rrd_filename, $fecInErrorsCount, $fecOutErrorsCount);
}

// Migrated to Wireless Sensor
$crcErrors = snmp_get($device, "fecCRCError.0", "-Ovqn", "WHISP-BOX-MIBV2-MIB");
if (is_numeric($crcErrors)) {
    $rrd_def = RrdDefinition::make()->addDataset('crcErrors', 'GAUGE', 0, 100000);
    $fields = array(
        'crcErrors' => $crcErrors,
    );
    $tags = compact('rrd_def');
    data_update($device, 'canopy-generic-crcErrors', $tags, $fields);
    $graphs['canopy_generic_crcErrors'] = true;
    unset($crcErrors);
}

$jitter = snmp_get($device, "jitter.0", "-Ovqn", "WHISP-SM-MIB");
if (is_numeric($jitter)) {
    $rrd_def = RrdDefinition::make()->addDataset('jitter', 'GAUGE', 0, 20);
    $fields = array(
        'jitter' => $jitter,
    );
    $tags = compact('rrd_def');
    data_update($device, 'canopy-generic-jitter', $tags, $fields);
    $graphs['canopy_generic_jitter'] = true;
    unset($rrd_filename, $jitter);
}

$multi_get_array = snmp_get_multi($device, ['regCount.0', 'regFailureCount.0'], "-OQU", "WHISP-APS-MIB");
d_echo($multi_get_array);
$registered = $multi_get_array[0]["WHISP-APS-MIB::regCount"];
$failed = $multi_get_array[0]["WHISP-APS-MIB::regFailureCount"];

if (is_numeric($registered) && is_numeric($failed)) {
    $rrd_def = RrdDefinition::make()
        ->addDataset('regCount', 'GAUGE', 0, 15000)
        ->addDataset('failed', 'GAUGE', 0, 15000);
    $fields = array(
        'regCount' => $registered,
        'failed' => $failed,
    );
    $tags = compact('rrd_def');
    data_update($device, 'canopy-generic-regCount', $tags, $fields);
    $graphs['canopy_generic_regCount'] = true;
    unset($rrd_filename, $registered, $failed);
}

$visible = str_replace('"', "", snmp_get($device, ".1.3.6.1.4.1.161.19.3.4.4.7.0", "-Ovqn", ""));
$tracked = str_replace('"', "", snmp_get($device, ".1.3.6.1.4.1.161.19.3.4.4.8.0", "-Ovqn", ""));
if (is_numeric($visible) && is_numeric($tracked)) {
    $rrd_def = RrdDefinition::make()
        ->addDataset('visible', 'GAUGE', 0, 1000)
        ->addDataset('tracked', 'GAUGE', 0, 1000);
    $fields = array(
        'visible' => floatval($visible),
        'tracked' => floatval($tracked),
    );
    $tags = compact('rrd_def');
    data_update($device, 'canopy-generic-gpsStats', $tags, $fields);
    $graphs['canopy_generic_gpsStats'] = true;
    unset($rrd_filename, $visible, $tracked);
}

$dbmRadio = str_replace('"', "", snmp_get($device, "radioDbmInt.0", "-Ovqn", "WHISP-SM-MIB"));
$minRadio = str_replace('"', "", snmp_get($device, "minRadioDbm.0", "-Ovqn", "WHISP-SM-MIB"));
$maxRadio = str_replace('"', "", snmp_get($device, "maxRadioDbm.0", "-Ovqn", "WHISP-SM-MIB"));
$avgRadio = str_replace('"', "", snmp_get($device, "radioDbmAvg.0", "-Ovqn", "WHISP-SM-MIB"));

if (is_numeric($dbmRadio) && is_numeric($minRadio) && is_numeric($maxRadio) && is_numeric($avgRadio)) {
    $rrd_def = RrdDefinition::make()
        ->addDataset('dbm', 'GAUGE', -100, 0)
        ->addDataset('min', 'GAUGE', -100, 0)
        ->addDataset('max', 'GAUGE', -100, 0)
        ->addDataset('avg', 'GAUGE', -100, 0);

    $fields = array(
        'dbm' => $dbmRadio,
        'min' => $minRadio,
        'max' => $maxRadio,
        'avg' => $avgRadio,
    );
    $tags = compact('rrd_def');
    data_update($device, 'canopy-generic-radioDbm', $tags, $fields);
    $graphs['canopy_generic_radioDbm'] = true;
    unset($rrd_filename, $dbmRadio, $minRadio, $maxRadio, $avgRadio);
}

$horizontal = str_replace('"', "", snmp_get($device, "linkRadioDbmHorizontal.2", "-Ovqn", "WHISP-APS-MIB"));
$vertical = str_replace('"', "", snmp_get($device, "linkRadioDbmVertical.2", "-Ovqn", "WHISP-APS-MIB"));
if (is_numeric($horizontal) && is_numeric($vertical)) {
    $rrd_def = RrdDefinition::make()
        ->addDataset('horizontal', 'GAUGE', -100, 0)
        ->addDataset('vertical', 'GAUGE', -100, 0);
    $fields = array(
        'horizontal' => $horizontal,
        'vertical' => $vertical,
    );
    $tags = compact('rrd_def');
    data_update($device, 'canopy-generic-450-linkRadioDbm', $tags, $fields);
    $graphs['canopy_generic_450_linkRadioDbm'] = true;
    unset($rrd_filename, $horizontal, $horizontal);
}

$lastLevel = str_replace('"', "", snmp_get($device, "lastPowerLevel.2", "-Ovqn", "WHISP-APS-MIB"));
if (is_numeric($lastLevel)) {
    $rrd_def = RrdDefinition::make()->addDataset('last', 'GAUGE', -100, 0);
    $fields = array(
        'last' => $lastLevel,
    );
    $tags = compact('rrd_def');
    data_update($device, 'canopy-generic-450-powerlevel', $tags, $fields);
    $graphs['canopy_generic_450_powerlevel'] = true;
    unset($lastLevel);
}

$vertical = str_replace('"', "", snmp_get($device, ".1.3.6.1.4.1.161.19.3.2.2.117.0", "-Ovqn", ""));
$horizontal = str_replace('"', "", snmp_get($device, ".1.3.6.1.4.1.161.19.3.2.2.118.0", "-Ovqn", ""));
$combined = snmp_get($device, "1.3.6.1.4.1.161.19.3.2.2.21.0", "-Ovqn", "");
if (is_numeric($vertical) && is_numeric($horizontal) && is_numeric($combined)) {
    $rrd_def = RrdDefinition::make()
        ->addDataset('vertical', 'GAUGE', -150, 0)
        ->addDataset('horizontal', 'GAUGE', -150, 0)
        ->addDataset('combined', 'GAUGE', -150, 0);
    $fields = array(
        'vertical' => floatval($vertical),
        'horizontal' => floatval($horizontal),
        'combined' => $combined,
    );
    $tags = compact('rrd_def');
    data_update($device, 'canopy-generic-signalHV', $tags, $fields);
    $graphs['canopy_generic_signalHV'] = true;
    unset($rrd_filename, $vertical, $horizontal, $combined);
}

$horizontal = str_replace('"', "", snmp_get($device, "radioDbmHorizontal.0", "-Ovqn", "WHISP-SM-MIB"));
$vertical = str_replace('"', "", snmp_get($device, "radioDbmVertical.0", "-Ovqn", "WHISP-SM-MIB"));
if (is_numeric($horizontal) && is_numeric($vertical)) {
    $rrd_def = RrdDefinition::make()
        ->addDataset('horizontal', 'GAUGE', -100, 100)
        ->addDataset('vertical', 'GAUGE', -100, 100);

    $fields = array(
        'horizontal' => $horizontal,
        'vertical' => $vertical,
    );
    $tags = compact('rrd_def');
    data_update($device, 'canopy-generic-450-slaveHV', $tags, $fields);
    $graphs['canopy_generic_450_slaveHV'] = true;
    unset($rrd_filename, $horizontal, $vertical);
}
