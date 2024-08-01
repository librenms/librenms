<?php
/*
 * LibreNMS module to Display data from F5 BigIP LTM Devices
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$component = new LibreNMS\Component();
$components = $component->getComponents($device['device_id'], ['filter' => ['ignore' => ['=', 0]]]);

// We only care about our device id.
$components = $components[$device['device_id']];

// We extracted all the components for this device, now lets only get the LTM ones.
$keep = [];
$types = [$module, 'f5-cert'];
foreach ($components as $k => $v) {
    foreach ($types as $type) {
        if ($v['type'] == $type) {
            $keep[$k] = $v;
        }
    }
}
$components = $keep;

/*
 * if (is_file('includes/html/pages/device/loadbalancer/' . $vars['subtype'] . '.inc.php')) {
 *     include 'includes/html/pages/device/loadbalancer/' . $vars['subtype'] . '.inc.php';
 * } else {
 *     include 'includes/html/pages/device/loadbalancer/ltm_pool_all.inc.php';
 * }//end if
 */
//echo '<script>console.log("' . $type . '");</script>';

?>
<table id='grid' data-toggle='bootgrid' class='table table-condensed table-responsive table-striped'>
    <thead>
    <tr>
        <th data-column-id="cert">Certificate</th>
        <th data-column-id="daysleft" data-type="numeric">Days left until expiration</th>
        <th data-column-id="status" data-visible="false">Status</th>
        <th data-column-id="message">Status</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($components as $cert => $array) {
        if ($array['type'] != 'f5-cert') {
            continue;
        }
        if ($array['status'] != 0) {
            $message = $array['error'];
            $status = 2;
        } else {
            $message = 'Ok';
            $status = '';
        } ?>
        <tr <?php echo $error; ?>>
            <td><?php echo $array['label']; ?></td>
            <td><?php echo $array['daysLeft']; ?></td>
            <td><?php echo $status; ?></td>
            <td><?php echo $message; ?></td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>
<script type="text/javascript">
    $("#grid").bootgrid({
        caseSensitive: false,
        statusMappings: {
            2: "danger"
        },
    })
</script>

