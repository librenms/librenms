<?php
/*
 * LibreNMS pre-cache module for Teleste Luminato
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
 * @copyright  2021 Peca Nesovanovic
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
echo 'Luminato ';

$oidt = snmp_walk($device, 'TransferEntry', '-OsQ', 'TELESTE-LUMINATO-MIB');
$oidt = trim($oidt);
$oidi = snmp_walk($device, 'IfExtEntry', '-OsQ', 'TELESTE-LUMINATO-MIB');
$oidi = trim($oidi);
$oids = $oidt . "\n" . $oidi;

if ($oids) {
    foreach (explode("\n", $oids) as $data) {
        if ($data) {
            $split = explode('=', $data);
            $value = trim($split[1]);
            $index = trim(explode('.', $split[0])[1]);
            $name = trim(explode('.', $split[0])[0]);
            $pre_cache['transfer'][$index][$name] = $value;
        }
    }
}
