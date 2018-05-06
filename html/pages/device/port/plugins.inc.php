<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
$pagetitle[] = 'Plugins';
$port_id_notes = 'port_id_notes:' . $port['port_id'];
$device_id = $device['device_id'];
$data = get_dev_attrib($device, $port_id_notes);
?>
<h3>Plugins</h3>
<hr>
<?php
    LibreNMS\Plugins::call('port_container', $device, $port);
?>
