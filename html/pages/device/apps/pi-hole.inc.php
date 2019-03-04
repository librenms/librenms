<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage pi-hole
 * @link       http://librenms.org
 * @copyright  2019 LibreNMS
 * @author     crcro <crc@nuamchefazi.ro>
*/

global $config;

$graphs = [
    'pi-hole_query_types' => 'Query Types',
    'pi-hole_destinations' => 'Destinations',
    'pi-hole_query_results' => 'Query Results',
    'pi-hole_block_percent' => 'Block Percentage',
    'pi-hole_blocklist' => 'Blocklist Domains'
];

include 'app.bootstrap.inc.php';
