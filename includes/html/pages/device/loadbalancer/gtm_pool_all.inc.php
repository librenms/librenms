<?php
/*
 * LibreNMS module to display F5 GTM Wide IP Details
 *
 * Adapted from F5 LTM module by Darren Napper
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

?>
<table id='grid' data-toggle='bootgrid' class='table table-condensed table-responsive table-striped'>
    <thead>
    <tr>
        <th data-column-id="gtmpoolid" data-type="numeric" data-visible="false">gtmpoolid</th>
        <th data-column-id="name">Name</th>
        <th data-column-id="status" data-visible="false">Status Code</th>
        <th data-column-id="message">Status</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($components as $wide_id => $array) {
        if ($array['type'] != 'f5-gtm-pool') {
            continue;
        }
        if ($array['status'] != 0) {
            $message = $array['error'];
            $status = 2;
        } else {
            $message = 'Ok';
            $status = '';
        } ?>
        <tr>
            <td><?php echo $wide_id; ?></td>
            <td><?php echo $array['label']; ?></td>
            <td><?php echo $status; ?></td>
            <td><?php echo $message; ?></td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>

<div class="panel panel-default" id="requests">
    <div class="panel-heading">
        <h3 class="panel-title">Resolved Requests</h3>
    </div>
    <div class="panel-body">
        <?php
        $graph_array = [];
        $graph_array['device'] = $device['device_id'];
        $graph_array['height'] = '100';
        $graph_array['width'] = '215';
        $graph_array['legend'] = 'no';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['type'] = 'device_bigip_gtm_allpool_requests';
        require 'includes/html/print-graphrow.inc.php';
        ?>
    </div>
</div>

<div class="panel panel-default" id="dropped">
    <div class="panel-heading">
        <h3 class="panel-title">Dropped Requests</h3>
    </div>
    <div class="panel-body">
        <?php
        $graph_array = [];
        $graph_array['device'] = $device['device_id'];
        $graph_array['height'] = '100';
        $graph_array['width'] = '215';
        $graph_array['legend'] = 'no';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['type'] = 'device_bigip_gtm_allpool_dropped';
        require 'includes/html/print-graphrow.inc.php';
        ?>
    </div>
</div>
    <script type="text/javascript">
        $("#grid").bootgrid({
            caseSensitive: false,
            statusMappings: {
                2: "danger"
            },
        }).on("click.rs.jquery.bootgrid", function (e, columns, row) {
            var link = '<?php echo \LibreNMS\Util\Url::generate($vars, ['type' => 'gtm_pool', 'subtype' => 'gtm_pool_det']); ?>gtmpoolid='+row['gtmpoolid'];
            window.location.href = link;
        });
    </script>
