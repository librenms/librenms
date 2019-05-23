<?php

/**
 * check_https.inc.php
 *
 * Uses check_http nagios pluggin for https.
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
 * @copyright  2019 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

if ($service['service_params']) {
    $params = $service['service_params'] . " -S";
} else {
    $params = " -S";
}


$check_cmd = $config['nagios_plugins'] . "/check_http -H " . ($service['service_ip'] ? $service['service_ip'] : $service['hostname']) . $params;
