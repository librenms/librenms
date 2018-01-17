<?php
/**
 * microsemipdsine.inc.php
 *
 * LibreNMS OS poller module for Microsemi PowerDsine PoE Midspans
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
 * @copyright  2017 Lorenzo Zafra
 * @author     Lorenzo Zafra<zafra@ualberta.;a>
 */
preg_match('~(?<hardware>.*)\..*=(?<serial>\d*)\.\s+\w+\s+\w+=(?<version>.*)~', $device['sysDescr'], $matches);

if ($matches['hardware'] == 'Midspan') {
    $hardware = 'PoE Midspan';
}

if ($matches['serial']) {
    $serial = $matches['serial'];
}

if ($matches['version']) {
    $data = explode(', ', $matches['version']);
    $version = $data[0];
}
