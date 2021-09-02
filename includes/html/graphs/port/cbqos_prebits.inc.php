<?php
/*
 * LibreNMS module to display Cisco Class-Based QoS Details
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$cbqos_parameter_name = 'prebits';
$cbqos_operator = '*';
$cbqos_operator_param = '8';
include 'includes/html/graphs/port/cbqos_generic.inc.php';
