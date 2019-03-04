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
    'squid_bytehit' => 'Byte Hits',
    'squid_reqhit' => 'Request Hits',
    'squid_http' => 'Client HTTP',
    'squid_httpbw' => 'Client HTTP Bandwidth',
    'squid_server' => 'Server HTTP',
    'squid_serverbw' => 'Server HTTP Bandwidth',
    'squid_clients' => 'Clients',
    'squid_cputime' => 'CPU Time',
    'squid_cpuusage' => 'CPU Usage',
    'squid_filedescr' => 'File Descriptors',
    'squid_memory' => 'Memory',
    'squid_objcount' => 'Object Count',
    'squid_pagefaults' => 'Pagefaults',
    'squid_sysnumread' => 'Sys Read',
];

include "app.bootstrap.inc.php";
