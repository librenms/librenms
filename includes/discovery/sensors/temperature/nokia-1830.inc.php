<?php
/**
 * LibreNMS - Nokia PSD SFP DDM Sensors
 *
 * @category   Network_Monitoring
 *
 * @author     Nick Peelman <nick@peelman.us>
 * @license    https://gnu.org/copyleft/gpl.html GNU GPL
 *
 * @link       https://github.com/librenms/librenms/
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/

// *************************************************************
// ***** Temperature Sensors for Nokia PSD
// *************************************************************

if (strpos($device['sysObjectID'], '.1.3.6.1.4.1.7483.1.3.1.12') !== false) {
  d_echo('Nokia PSD DDM Temperature Sensors\n');
  $ifIndexToName = SnmpQuery::cache()->walk('IF-MIB::ifName')->pluck();
  $ifAdminStatus = SnmpQuery::cache()->enumStrings()->walk('IF-MIB::ifAdminStatus')->pluck();
  $ifDdmValues = SnmpQuery::cache()->hideMib()->walk('TROPIC-PSD-MIB::tnPsdDdmDataValue')->table(2);

  foreach ($ifDdmValues as $ifIndex => $ddmvalue) {
    $ifName = $ifIndexToName[$ifIndex] ?? $ifIndex;
    if (! empty($ddmvalue['ddmTemperature']['tnPsdDdmDataValue']) && $ifAdminStatus[$ifIndex] == 'up') {
          $divisor = 10;
          $descr = $ifName;
          app('sensor-discovery')->discover(new \App\Models\Sensor([
              'poller_type' => 'snmp',
              'sensor_class' => 'temperature',
              'sensor_oid' => ".1.3.6.1.4.1.7483.2.2.7.3.1.4.1.2.$ifIndex.2",
              'sensor_index' => "$ifIndex.2",
              'sensor_type' => 'nokia-1830',
              'sensor_descr' => $descr,
              'sensor_divisor' => $divisor,
              'sensor_multiplier' => 1,
              'sensor_current' => $ddmvalue['ddmTemperature']['tnPsdDdmDataValue'] / $divisor,
              'entPhysicalIndex' => $ifIndex,
              'entPhysicalIndex_measured' => 'port',
              'group' => 'Transceivers',
          ]));
      }
  }
}   //  ************** End of Sensors for Nokia PSD **********
