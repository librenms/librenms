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

$cambium_type = $poll_device['sysDescr'];
$PMP = snmp_get($device, 'boxDeviceType.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
$version = $cambium_type;

$filtered_words = array(
    'timing',
    'timeing'
);

$models = array(
    'BHUL450'   => 'PTP 450',
    'BHUL'      => 'PTP 230',
    'BH20'      => 'PTP 100',
    'MIMO OFDM' => 'PMP 450',
    'OFDM'      => 'PMP 430',
    'AP'        => 'PMP 100'
);

foreach ($models as $desc => $model) {
    if (str_contains($cambium_type, $desc)) {
        $hardware = $model;

        if (str_contains($model, 'PTP')) {
            $masterSlaveMode = str_replace($filtered_words, "", snmp_get($device, 'bhTimingMode.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB'));
            $hardware = $model . ' '. $masterSlaveMode;
            $version = snmp_get($device, 'boxDeviceTypeID.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
        }

        if (str_contains($model, 'PMP')) {
            if (str_contains($version, "AP")) {
                $hardware = $model . ' AP';
            } elseif (str_contains($version, "SM")) {
                $hardware = $model . ' SM';
            }
        }
    }
}
