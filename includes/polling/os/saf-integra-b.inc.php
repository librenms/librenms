<?php
/**
 * saf-integra-b.inc.php
 *
 * Saf Integra B Polling module
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

preg_match('/Prod: ([A-Za-z-_]+);Vers: ([0-9.]+);.*;S\/N: ([0-9]+)/', $device['sysDescr'], $matches);

$hardware = $matches[1];
$version = $matches[2];
$serial = $matches[3];
