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

include 'includes/html/pages/device/loadbalancer/gtm_wide_common.inc.php';

if ($components[$vars['wideid']]['type'] == 'f5-gtm-wide') {
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="container-fluid">
                <div class='row'>
                    <div class="panel panel-default" id="requests">
                        <div class="panel-heading">
                            <h3 class="panel-title">Total Requests</h3>
                        </div>
                        <div class="panel-body">
                            <?php
                            $graph_array = [];
    $graph_array['device'] = $device['device_id'];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['legend'] = 'no';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['type'] = 'device_bigip_gtm_wide_requests';
    $graph_array['id'] = $vars['wideid'];
    require 'includes/html/print-graphrow.inc.php'; ?>
                        </div>
                    </div>

                    <div class="panel panel-default" id="resolved">
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
    $graph_array['type'] = 'device_bigip_gtm_wide_resolved';
    $graph_array['id'] = $vars['wideid'];
    require 'includes/html/print-graphrow.inc.php'; ?>
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
    $graph_array['type'] = 'device_bigip_gtm_wide_dropped';
    $graph_array['id'] = $vars['wideid'];
    require 'includes/html/print-graphrow.inc.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
}
