<?php
/**
 * sinetica.inc.php
 *
 * LibreNMS os polling module for Sinetica
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

// Sinetica UPSController. Versions: App. 6.04.03,   OS 6.3,   Btldr 1.06.09,   H/w ZBBNC2 Rev 1.01.06

[$os_temp, $os_ver, $btldr, $hardware_temp] = explode(',   ', $device['sysDescr']);

[$ignore, $version] = explode('App. ', $os_temp);
$hardware = preg_replace('/H\/w /', '', $hardware_temp);
