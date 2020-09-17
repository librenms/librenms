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
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

$pagetitle[] = 'Services Templates';

require_once 'includes/html/modal/new_service_template.inc.php';
require_once 'includes/html/modal/delete_service_template.inc.php';
?>
<div class="container-fluid">
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">
                <span style="font-weight: bold;">Services Templates</span> &#187;

                <?php
                $menu_options = array(
                    'basic' => 'Basic',
                );

                if (!$vars['view']) {
                    $vars['view'] = 'basic';
                }

                // The menu option - on the left

                $sep = '';

                foreach ($menu_options as $option => $text) {
                    if (empty($vars['view'])) {
                        $vars['view'] = $option;
                    }

                    echo $sep;
                    if ($vars['view'] == $option) {
                        echo "<span class='pagemenu-selected'>";
                    }

                    echo generate_link($text, $vars, array(
                        'view' => $option
                    ));
                    if ($vars['view'] == $option) {
                        echo '</span>';
                    }

                    $sep = ' | ';
                }

                unset($sep);

                // The status option - on the right

                echo '<div class="pull-right">';
                $sep = '';
                unset($sep);
                echo '</div>';
                echo '</div>';
                echo '<div style="margin:10px 10px 0px 10px;" id="message"></div>';
                echo '<div class="panel-body">';

                $sql_param = array();

                $host_par = array();
                $perms_sql = null;
                if (!Auth::user()->hasGlobalRead()) {
                    $device_group_ids = Permissions::devicesForUser()->toArray() ?: [0];
                    $perms_sql .= " AND `D`.`device_group_id` IN " .dbGenPlaceholders(count($device_group_ids));
                    $host_par = $device_group_ids;
                }

                $host_sql = 'SELECT `D`.`id`,`D`.`name` FROM device_groups AS D, services_template AS S WHERE D.id = S.device_group_id ' . $perms_sql . ' GROUP BY `D`.`name`, `D`.`id` ORDER BY D.`name`';

                $shift = 1;
                foreach (dbFetchRows($host_sql, $host_par) as $device_group) {
                    $device_group_id = $device_group['id'];
                    $device_group_name = $device_group['name'];
                    #$device_sysName = $device_group['name'];
                    #$devlink = generate_device_link($device_group, null, array('tab' => 'services'));
                    if ($shift == 1) {
                        array_unshift($sql_param, $device_group_id);
                        $shift = 0;
                    } else {
                        $sql_param[0] = $device_group_id;
                    }

                    $header = true;
                    $footer = false;

                    $service_template_iteration = 0;
                    $services_template = dbFetchRows("SELECT * FROM `services_template` WHERE `device_group_id` = ? $where ORDER BY service_template_type", $sql_param);
                    $services_template_count = count($services_template);
                    foreach ($services_template as $service_template) {
                        $service_template_iteration++;

                        if ($service_template_iteration < 2 && $header) {
                            echo '<div class="panel panel-default">';
                            echo '<div class="panel-heading"><h3 class="panel-title">' . $device_group_id . '</h3>' . $device_group_name . '</div>';
                            echo '<div class="panel-body">';
                            echo '<table class="table table-hover table-condensed">';
                            echo '<thead>';
                            echo '<th style="width:1%;max-width:1%;"></th>';
                            echo '<th style="width:10%;max-width: 10%;">Service</th>';
                            echo '<th style="width:15%;max-width: 15%;">Last Changed</th>';
                            echo '<th style="width:15%;max-width: 15%;">Description</th>';
                            echo '<th >Message</th>';
                            echo '<th style="width:5%;max-width:5%;"></th>';
                            echo '</thead>';
                        }

                        $header = false;

                        echo '<tr id="row_' . $service_template['service_template_id'] . '">';
                        echo '<td><span data-toggle="tooltip" title="' . $title . '" class="alert-status ' . $label . '"></span></td>';
                        echo '<td>' . nl2br(display($service_template['service_template_type'])) . '</td>';
                        echo '<td>' . formatUptime(time() - $service_template['service_template_changed']) . '</td>';
                        echo '<td>' . nl2br(display($service_template['service_template_desc'])) . '</td>';
                        echo '<td>' . nl2br(display($service_template['service_template_message'])) . '</td>';

                        if (Auth::user()->hasGlobalAdmin()) {
                            echo "<td>
                                    <button type='button' class='btn btn-warning btn-sm' aria-label='Apply' data-toggle='modal' data-target='#discover-service-template' data-service_template_id='{$service_template['service_template_id']}' data-device_group_id='{device_group['device_group_id']}' name='discover-service-template'><i class='fa fa-pencil' aria-hidden='true'></i></button>
                                    <button type='button' class='btn btn-primary btn-sm' aria-label='Edit' data-toggle='modal' data-target='#create-service-template' data-service_template_id='{$service_template['service_template_id']}' name='edit-service-template'><i class='fa fa-pencil' aria-hidden='true'></i></button>
                                    <button type='button' class='btn btn-danger btn-sm' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-service_template_id='{$service_template['service_template_id']}' name='delete-service-template'><i class='fa fa-trash' aria-hidden='true'></i></button>
                                    </td>";
                        }
                        echo '</tr>';

                        if ($service_template_iteration >= $services_template_count) {
                            $footer = true;
                        }

                        if ($footer) {
                            echo '</table>';
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                }
                unset($samehost);
                ?>
            </div>
        </div>
    </div>
</div>
