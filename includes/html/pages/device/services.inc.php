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
    echo '<td class="col-sm-2"><strong>Name</strong></td>';
    echo '<td class="col-sm-1"><strong>Check Type</strong></td>';
    echo '<td class="col-sm-1"><strong>Remote Host</strong></td>';
    echo '<td class="col-sm-4"><strong>Message</strong></td>';
    echo '<td class="col-sm-2"><strong>Description</strong></td>';
    echo '<td class="col-sm-1"><strong>Last Changed</strong></td>';
    echo '<td class="col-sm-1"></td>';
    echo '</thead>';

    foreach ($services as $service) {
        $service['service_ds'] = htmlspecialchars_decode($service['service_ds']);
        if ($service['service_status'] == '2') {
            $status_label = 'label-danger';
        } elseif ($service['service_status'] == '1') {
            $status_label = 'label-warning';
        } elseif ($service['service_status'] == '0') {
            $status_label = 'label-success';
        } else {
            $status_label = 'label-info';
        }

        echo '<tr id="row_' . $service['service_id'] . '">';
        echo '<td class="col-sm-2"><span class="alert-status ' . $status_label . '"><span class="device-services-page text-nowrap">' . htmlentities($service['service_name']) . '</span></span></td>';
        echo '<td class="col-sm-1 text-muted">' . htmlentities($service['service_type']) . '</td>';
        echo '<td class="col-sm-1 text-muted">' . nl2br(htmlentities($service['service_ip'])) . '</td>';
        echo '<td class="col-sm-4">' . nl2br(htmlentities(trim($service['service_message']))) . '</td>';
        echo '<td class="col-sm-2 text-muted">' . htmlentities($service['service_desc']) . '</td>';
        echo '<td class="col-sm-1 text-muted">' . \LibreNMS\Util\Time::formatInterval(time() - $service['service_changed']) . '</td>';
        echo '<td class="col-sm-1">';
        if (Auth::user()->hasGlobalAdmin()) {
            echo '<div class="pull-right">';
            echo "<button type='button' class='btn btn-primary btn-sm' aria-label='Edit' data-toggle='modal' data-target='#create-service' data-service_id='{$service['service_id']}' name='edit-service'><i class='fa fa-pencil' aria-hidden='true'></i></button>";
            echo "<button type='button' class='btn btn-danger btn-sm' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-service_id='{$service['service_id']}' name='delete-service'><i class='fa fa-trash' aria-hidden='true'></i></button";
            echo '</div>';
        }
        echo '</td>';
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
                echo '<td colspan="7">';

                include 'includes/html/print-graphrow.inc.php';

                echo '</td>';
                echo '</tr>';
            }
        }
    }
    echo '</table>';
} else {
    echo '<div class="device-services-page-no-service">No Services</div>';
}

echo '</div>';
