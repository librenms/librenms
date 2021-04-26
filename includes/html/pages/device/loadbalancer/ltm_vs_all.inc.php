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

?>
<table id='grid' data-toggle='bootgrid' class='table table-condensed table-responsive table-striped'>
    <thead>
    <tr>
        <th data-column-id="vsid" data-type="numeric" data-visible="false">vsid</th>
        <th data-column-id="poolid" data-type="numeric" data-visible="false">poolid</th>
        <th data-column-id="name">Name</th>
        <th data-column-id="host">IP : Port</th>
        <th data-column-id="pool">Default Pool</th>
        <th data-column-id="status" data-visible="false">Status</th>
        <th data-column-id="message">Status</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($components as $vs_id => $array) {
        if ($array['type'] != 'f5-ltm-vs') {
            continue;
        }
        $string = $array['IP'] . ':' . $array['port'];
        if ($array['status'] != 0) {
            $message = $array['error'];
            $status = 2;
        } else {
            $message = 'Ok';
            $status = '';
        }

        // Find the ID for this pool
        $id = 0;
        foreach ($components as $k => $v) {
            if ($v['type'] != 'f5-ltm-pool') {
                continue;
            }
            if ($v['label'] == $array['pool']) {
                $id = $k;
            }
        } ?>
        <tr>
            <td><?php echo $vs_id; ?></td>
            <td><?php echo $id; ?></td>
            <td><?php echo $array['label']; ?></td>
            <td><?php echo $string; ?></td>
            <td><?php echo $array['pool']; ?></td>
            <td><?php echo $status; ?></td>
            <td><?php echo $message; ?></td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>

<div class="panel panel-default" id="connections">
    <div class="panel-heading">
        <h3 class="panel-title">Connections</h3>
    </div>
    <div class="panel-body">
        <?php
        $graph_array = [];
        $graph_array['device'] = $device['device_id'];
        $graph_array['height'] = '100';
        $graph_array['width'] = '215';
        $graph_array['legend'] = 'no';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['type'] = 'device_bigip_ltm_allvs_conns';
        require 'includes/html/print-graphrow.inc.php';
        ?>
    </div>
</div>

<div class="panel panel-default" id="bytesin">
    <div class="panel-heading">
        <h3 class="panel-title">Bytes In</h3>
    </div>
    <div class="panel-body">
        <?php
        $graph_array = [];
        $graph_array['device'] = $device['device_id'];
        $graph_array['height'] = '100';
        $graph_array['width'] = '215';
        $graph_array['legend'] = 'no';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['type'] = 'device_bigip_ltm_allvs_bytesin';
        require 'includes/html/print-graphrow.inc.php';
        ?>
    </div>
</div>

<div class="panel panel-default" id="bytesout">
    <div class="panel-heading">
        <h3 class="panel-title">Bytes Out</h3>
    </div>
    <div class="panel-body">
        <?php
        $graph_array = [];
        $graph_array['device'] = $device['device_id'];
        $graph_array['height'] = '100';
        $graph_array['width'] = '215';
        $graph_array['legend'] = 'no';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['type'] = 'device_bigip_ltm_allvs_bytesout';
        require 'includes/html/print-graphrow.inc.php';
        ?>
    </div>
</div>

<div class="panel panel-default" id="pktsin">
    <div class="panel-heading">
        <h3 class="panel-title">Packets In</h3>
    </div>
    <div class="panel-body">
        <?php
        $graph_array = [];
        $graph_array['device'] = $device['device_id'];
        $graph_array['height'] = '100';
        $graph_array['width'] = '215';
        $graph_array['legend'] = 'no';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['type'] = 'device_bigip_ltm_allvs_pktsin';
        require 'includes/html/print-graphrow.inc.php';
        ?>
    </div>
</div>

<div class="panel panel-default" id="pktsout">
    <div class="panel-heading">
        <h3 class="panel-title">Packets Out</h3>
    </div>
    <div class="panel-body">
        <?php
        $graph_array = [];
        $graph_array['device'] = $device['device_id'];
        $graph_array['height'] = '100';
        $graph_array['width'] = '215';
        $graph_array['legend'] = 'no';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['type'] = 'device_bigip_ltm_allvs_pktsout';
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
            var link = '<?php echo \LibreNMS\Util\Url::generate($vars, ['type' => 'ltm_vs', 'subtype' => 'ltm_vs_det']); ?>vsid='+row['vsid']+'/poolid='+row['poolid'];
            window.location.href = link;
        });
    </script>
