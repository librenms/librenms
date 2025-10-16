<?php

/*
 * LibreNMS discovery module for Terra-sdi480 inputs SAT signal
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 *
 * @copyright  2022 Peca Nesovanovic
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
$divisor = 10000;
$multiplier = 1;

$oids = SnmpQuery::cache()->hideMib()->walk('TERRA-sdi480-MIB::sdi480status')->table(1);

if (is_array($oids)) {
    d_echo('Terra sdi480 tv_signal');
    for ($inputid = 1; $inputid <= 8; $inputid++) {
        $signal = $oids[0]['inlevel' . $inputid];
        if ($signal) {
            $oid = '.1.3.6.1.4.1.30631.1.17.1.' . $inputid . '.2.0';
            $type = 'terra_tvsignal';
            $descr = 'Level# ' . sprintf('%02d', $inputid);
            $limit = 0.085;
            $limitwarn = 0.080;
            $lowwarnlimit = 0.050;
            $lowlimit = 0.045;
            $value = $signal / $divisor;

            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'tv_signal',
                'sensor_oid' => $oid,
                'sensor_index' => $inputid,
                'sensor_type' => $type,
                'sensor_descr' => $descr,
                'sensor_divisor' => $divisor,
                'sensor_multiplier' => $multiplier,
                'sensor_limit_low' => $lowlimit,
                'sensor_limit_low_warn' => $lowwarnlimit,
                'sensor_limit_warn' => $limitwarn,
                'sensor_limit' => $limit,
                'sensor_current' => $value,
                'entPhysicalIndex' => null,
                'entPhysicalIndex_measured' => null,
                'user_func' => null,
                'group' => 'Inputs',
            ]));
        }
    }
}
