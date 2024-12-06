<?php
/*
 * LibreNMS discovery module for Terra-sdi480 inputs SNR
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
$divisor = 10;
$multiplier = 1;

if (is_array($pre_cache['sdi480status'])) {
    d_echo('Terra sdi480 SNR');
    for ($inputid = 1; $inputid <= 8; $inputid++) {
        $snr = $pre_cache['sdi480status'][0]['insnr' . $inputid];
        if ($snr) {
            $oid = '.1.3.6.1.4.1.30631.1.17.1.' . $inputid . '.3.0';
            $type = 'terra_snr';
            $descr = 'SNR# ' . sprintf('%02d', $inputid);
            $limit = 30;
            $limitwarn = 30;
            $lowwarnlimit = 14;
            $lowlimit = 12;
            $value = $snr / $divisor;
            $group = 'Inputs';
            discover_sensor(
                null,
                'snr',
                $device,
                $oid,
                $inputid,
                $type,
                $descr,
                $divisor,
                $multiplier,
                $lowlimit,
                $lowwarnlimit,
                $limitwarn,
                $limit,
                $value,
                'snmp',
                null,
                null,
                null,
                $group
            );
        }
    }
}
