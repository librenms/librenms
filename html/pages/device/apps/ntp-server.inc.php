<?php
/*
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
* @subpackage webui
* @link       http://librenms.org
* @copyright  2019 LibreNMS
* @author     LibreNMS Contributors
*/

global $config;

$graphs = [
    'ntp-server_stats' => 'NTPD Server - Statistics',
    'ntp-server_freq' => 'NTPD Server - Frequency',
    'ntp-server_stratum' => 'NTPD Server - Stratum',
    'ntp-server_buffer' => 'NTPD Server - Buffer',
    'ntp-server_bits' => 'NTPD Server - Packets Sent/Received',
    'ntp-server_packets' => 'NTPD Server - Packets Dropped/Ignored',
    'ntp-server_uptime' => 'NTPD Server - Uptime',
];

include "app.bootstrap.inc.php";
