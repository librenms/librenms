<?php
/**
 * comware.inc.php
 *
 * LibreNMS mempools discovery module for Comware
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

if ($device['os'] === 'comware') {
    echo 'hh3cEntityExtMemUsage: ';

    $entphydata = dbFetchRows("SELECT `entPhysicalIndex`, `entPhysicalClass`, `entPhysicalName` FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalClass` = 'module' ORDER BY `entPhysicalIndex`", array($device['device_id']));

    if ($entphydata) {
        $comware_mem = snmpwalk_cache_oid($device, 'hh3cEntityExtMemUsage', null, 'HH3C-ENTITY-EXT-MIB');

        foreach ($entphydata as $index) {
            if (is_numeric($comware_mem[$index['entPhysicalIndex']]['hh3cEntityExtMemUsage']) && is_numeric($index['entPhysicalIndex']) && $comware_mem[$index['entPhysicalIndex']]['hh3cEntityExtMemUsage'] > 0) {
                discover_mempool($valid_mempool, $device, $index['entPhysicalIndex'], 'comware', $index['entPhysicalName'], '1', null, null);
            }
        }
    }
    unset(
        $entphydata,
        $comware_mem
    );
}
