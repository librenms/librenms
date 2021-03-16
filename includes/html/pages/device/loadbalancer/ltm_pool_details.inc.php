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

// Determine a pool to show.
if (! isset($vars['poolid'])) {
    foreach ($components as $id => $array) {
        if ($array['type'] != 'f5-ltm-pool') {
            continue;
        }
        $vars['poolid'] = $id;
    }
}

if ($components[$vars['poolid']]['type'] == 'f5-ltm-pool') {
    $array = $components[$vars['poolid']];
    // Define some error messages
    $error_poolaction = [];
    $error_poolaction[0] = 'Unused';
    $error_poolaction[1] = 'Reboot';
    $error_poolaction[2] = 'Restart';
    $error_poolaction[3] = 'Failover';
    $error_poolaction[4] = 'Failover and Restart';
    $error_poolaction[5] = 'Go Active';
    $error_poolaction[6] = 'None';

    $parent = $array['UID']; ?>
    <div class="row">
        <div class="col-md-6">
            <div class="container-fluid">
                <div class='row'>
                        <div class='panel panel-default panel-condensed'>
                            <div class='panel-heading'>
                                <strong>Pool: <?php echo $array['label']; ?></strong></div>
                            <table class="table table-hover table-condensed table-striped">
                                <tr>
                                    <td>Minimum Active Servers:</td>
                                    <td><?php echo $array['minup']; ?></td>
                                </tr>
                                <tr>
                                    <td>Current Active Servers:</td>
                                    <td><?php echo $array['currentup']; ?></td>
                                </tr>
                                <tr>
                                    <td>Pool Down Action:</td>
                                    <td><?php echo $error_poolaction[$array['minupaction']]; ?></td>
                                </tr>
                                <tr>
                                    <td>Pool Monitor:</td>
                                    <td><?php echo $array['monitor']; ?></td>
                                </tr>
                            </table>
                        </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="container-fluid">
                <div class='row'>
                        <div class="panel panel-default panel-condensed">
                            <div class="panel-heading">
                                <strong>Pool Members</strong>
                            </div>
                            <table class="table table-hover table-condensed table-striped">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>IP : Port</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <?php
                                foreach ($components as $comp) {
                                    if ($comp['type'] != 'f5-ltm-poolmember') {
                                        continue;
                                    }
                                    if (! strstr($comp['UID'], $parent)) {
                                        continue;
                                    }

                                    $string = $comp['IP'] . ':' . $comp['port'];
                                    if ($comp['status'] != 0) {
                                        $status = $comp['error'];
                                        $error = 'class="danger"';
                                    } else {
                                        $status = 'Ok';
                                        $error = '';
                                    } ?>
                                    <tr <?php echo $error; ?>>
                                        <td><?php echo $comp['nodename']; ?></td>
                                        <td><?php echo $string; ?></td>
                                        <td><?php echo $status; ?></td>
                                    </tr>
                                    <?php
                                } ?>
                            </table>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="container-fluid">
                <div class='row'>
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
    $graph_array['type'] = 'device_bigip_ltm_allpm_conns';
    $graph_array['id'] = $vars['poolid'];
    require 'includes/html/print-graphrow.inc.php'; ?>
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
    $graph_array['type'] = 'device_bigip_ltm_allpm_bytesin';
    $graph_array['id'] = $vars['poolid'];
    require 'includes/html/print-graphrow.inc.php'; ?>
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
    $graph_array['type'] = 'device_bigip_ltm_allpm_bytesout';
    $graph_array['id'] = $vars['poolid'];
    require 'includes/html/print-graphrow.inc.php'; ?>
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
    $graph_array['type'] = 'device_bigip_ltm_allpm_pktsin';
    $graph_array['id'] = $vars['poolid'];
    require 'includes/html/print-graphrow.inc.php'; ?>
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
    $graph_array['type'] = 'device_bigip_ltm_allpm_pktsout';
    $graph_array['id'] = $vars['poolid'];
    require 'includes/html/print-graphrow.inc.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
}
