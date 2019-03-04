<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       http://librenms.org
 * @copyright  2019 LibreNMS
 * @author     LibreNMS Contributors
*/

if (empty($graph_array['type'])) {
    $graph_array['type'] = $graph_type;
}
if (empty($graph_array['device'])) {
    $graph_array['device'] = $device['device_id'];
}

print_optionbar_start();
echo "<span style='font-weight: bold;'>" . $graph_title . "</span>";
print_optionbar_end();

echo "<div class='panel-body'>";
require 'includes/print-graphrow.inc.php';
echo '</div>';
