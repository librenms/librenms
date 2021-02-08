<?php
/**
 * onefs.inc.php
 *
 * LibreNMS storage module for OneFS
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
$onefs_oids = snmp_get_multi_oid($device, ['ifsTotalBytes.0', 'ifsUsedBytes.0', 'ifsAvailableBytes.0'], '-OUQn', 'ISILON-MIB');
$storage['free'] = $onefs_oids['.1.3.6.1.4.1.12124.1.3.3.0'];
$storage['size'] = $onefs_oids['.1.3.6.1.4.1.12124.1.3.1.0'];
$storage['used'] = $onefs_oids['.1.3.6.1.4.1.12124.1.3.2.0'];
$storage['units'] = 1024;
unset($onefs_oids);
