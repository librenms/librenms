<?php
/**
 * dd-wrt.inc.php
 *
 * LibreNMS os polling module for Tomato
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

// dd-wrt, cannot use exec with OID specified. Options are extend (w/OID), or exec (w/o OID)
// -> extend seems to be the recommended approach, so use that (changes OID, which "spells out" name)
list($ignore, $version) = explode(' ', snmp_get($device, 'NET-SNMP-EXTEND-MIB::nsExtendOutput1Line."distro"', '-Osqnv'));
$hardware = snmp_get($device, 'NET-SNMP-EXTEND-MIB::nsExtendOutput1Line."hardware"', '-Osqnv');

unset($ignore);
