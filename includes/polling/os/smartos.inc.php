<?php
/**
 * smartos.inc.php
 *
 * LibreNMS os polling module for SmartOptics SmartOS
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
 * @link       https://www.librenms.org
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

//SmartOptics, M-Series M-1601-D1000C1 R2A, SmartOS v2.4.14 (Compiled on Thu Jun  2 14:21:33 CEST 2016)

[, $hardware, $version] = explode(',', $device['sysDescr']);

$hardware = str_replace('M-Series ', '', $hardware);
[,,$version,] = explode(' ', $version);
