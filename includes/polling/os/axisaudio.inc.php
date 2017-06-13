<?php
/**
 * $axisaudio.inc.php
 *
 * LibreNMS OS poller module for Axis Audio Appliances
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
 * @author     Lorenzo Zafra<zafra@ualberta.ca>
 */

// .1.3.6.1.2.1.1.1.0 = STRING:  ; AXIS P8221; Network IO Audio Module; 5.10.3; Jan 29 2016 10:47; 174; 1;

$data = explode('; ', $poll_device['sysDescr']);

if (isset($data[1])) {
    $hardware = $data[1];
}

if (isset($data[3])) {
    $version = $data[3];
}
