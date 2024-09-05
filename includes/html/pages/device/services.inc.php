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
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

$pagetitle[] = 'Services';

require_once 'includes/services.inc.php';
$services = service_get($device['device_id']);

require_once 'includes/html/modal/new_service.inc.php';
require_once 'includes/html/modal/delete_service.inc.php';

if (! $vars['view']) {
    $vars['view'] = 'basic';
}

$menu_options = [
    'basic' => 'Basic',
    'details' => 'Details',
];

echo '<br>';
echo '<div class="panel panel-default">';
echo '<div class="panel-heading">';
echo '<span style="font-weight: bold;">Services</span> &#187; ';

$sep = '';
foreach ($menu_options as $option => $text) {
    if (empty($vars['view'])) {
        $vars['view'] = $option;
    }

    echo $sep;
    if ($vars['view'] == $option) {
        echo '<span class="pagemenu-selected">';
    }

    echo generate_link($text, $vars, ['view' => $option]);
    if ($vars['view'] == $option) {
        echo '</span>';
    }

    $sep = ' | ';
}
unset($sep);

if (Auth::user()->hasGlobalAdmin()) {
    echo '<div class="pull-right"><a data-toggle="modal" href="#create-service"><i class="fa fa-cog" style="color:green" aria-hidden="true"></i> Add Service</a></div>';
}

echo '</div><div>';

if (count($services) > '0') {
    // Loop over each service, pulling out the details.

    echo '<table class="table table-hover table-condensed">';
    echo '<thead>';
    echo '<th style="width:1%;max-width:1%;"></th>';
    echo '<th style="width:10%;max-width: 10%;">Name</th>';
    echo '<th style="width:10%;max-width: 10%;">Check Type</th>';
    echo '<th >Message</th>';
    echo '<th style="width:16%;max-width: 25%;">Description</th>';
    echo '<th style="width:15%;max-width: 15%;">Last Changed</th>';
    echo '<th style="width:100px;max-width: 100px;"></th>';
    echo '</thead>';

    foreach ($services as $service) {
        $service['service_ds'] = htmlspecialchars_decode($service['service_ds']);
        if ($service['service_status'] == '2') {
            $status_title = 'CRITICAL';
            $status_label = 'danger';
        } elseif ($service['service_status'] == '1') {
            $status_title = 'WARNING';
            $status_label = 'warning';
        } elseif ($service['service_status'] == '0') {
            $status_title = 'OK';
            $status_label = 'success';
        } else {
            $status_title = 'UNKNOWN';
            $status_label = 'info';
        }

        echo '<tr>';
        echo '<td><span data-toggle="tooltip" title="' . $status_title . '" class="alert-status label-' . $status_label . '"></span></td>';
        echo '<td>' . $service['service_name'] . '</td>';
        echo '<td>' . $service['service_type'] . '</td>';
        echo '<td>' . $service['service_message'] . '</td>';
        echo '<td>' . $service['service_desc'] . '</td>';
        echo '<td>' . \LibreNMS\Util\Time::formatInterval(time() - $service['service_changed']) . '</td>';
        echo '<td>';
        echo '<div class="pull-right">';
        
        if (Auth::user()->hasGlobalAdmin()) {
            echo "<button type='button' class='btn btn-primary btn-sm' aria-label='Edit' data-toggle='modal' data-target='#create-service' data-service_id='{$service['service_id']}' name='edit-service'><i class='fa fa-pencil' aria-hidden='true'></i></button>
        <button type='button' class='btn btn-danger btn-sm' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-service_id='{$service['service_id']}' name='delete-service'><i class='fa fa-trash' aria-hidden='true'></i></button";
        }
        echo '</div>';
        echo '</tr>';

        if ($vars['view'] == 'details') {
            // if we have a script for this check, use it.
            $check_ds = null;
            $check_script = \LibreNMS\Config::get('install_dir') . '/includes/services/check_' . strtolower($service['service_type']) . '.inc.php';
            if (is_file($check_script)) {
                include $check_script;

                // If we have a replacement DS use it.
                if (isset($check_ds)) {
                    $service['service_ds'] = $check_ds;
                }
            }

            $graphs = json_decode($service['service_ds'], true);
            foreach ($graphs as $k => $v) {
                $graph_array['device'] = $device['device_id'];
                $graph_array['type'] = 'service_graph';
                $graph_array['id'] = $service['service_id'];
                $graph_array['ds'] = $k;

                echo '<tr>';
                echo '<td colspan="7"><div class="col-sm-12">';

                include 'includes/html/print-graphrow.inc.php';

                echo '</div></td>';
                echo '</tr>';
            }
        }
    }
    echo '</table>';
} else {
    echo '<div class="device-services-page-no-service">No Services</div>';
}

echo '</div>';
