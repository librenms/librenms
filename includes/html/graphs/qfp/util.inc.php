<?php
/**
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 LibreNMS
 * @author     Pavle Obradovic <pobradovic08@gmail.com>
 */
$scale_min = '0';
$scale_max = '100';

$ds = 'ProcessingLoad';

$colour_line = 'cc0000';
$colour_area = 'FFBBBB';
$colour_minmax = 'c5c5c5';

$graph_max = 1;
$unit_text = 'Utilization';
$line_text = $components['name'];

require 'includes/html/graphs/generic_simplex.inc.php';
