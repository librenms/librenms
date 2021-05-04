<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage nfs-server
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     SvennD <svennd@svennd.be>
*/

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$ds = 'net_tcpconn';
$colour_area = '9DDA52';
$colour_line = '2EAC6D';
$colour_area_max = 'FFEE99';
$graph_max = 10000;
$unit_text = 'net tcp connections';

$rrd_filename = Rrd::name($device['hostname'], ['app', 'nfs-server-default', $app['app_id']]);

require 'includes/html/graphs/generic_simplex.inc.php';
