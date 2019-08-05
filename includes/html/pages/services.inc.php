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

$pagetitle[] = 'Services';

require_once 'includes/html/modal/new_service.inc.php';
require_once 'includes/html/modal/delete_service.inc.php';
?>
<div class="container-fluid">
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">
                <span style="font-weight: bold;">Services</span> &#187;

                <?php
                $menu_options = array(
                    'basic' => 'Basic',
                );

                if (!$vars['view']) {
                    $vars['view'] = 'basic';
                }

                $status_options = array(
                    'all' => 'All',
                    'ok' => 'Ok',
                    'warning' => 'Warning',
                    'critical' => 'Critical',
                );

                if (!$vars['state']) {
                    $vars['state'] = 'all';
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

                foreach ($status_options as $option => $text) {
                    if (empty($vars['state'])) {
                        $vars['state'] = $option;
                    }

                    echo $sep;
                    if ($vars['state'] == $option) {
                        echo "<span class='pagemenu-selected'>";
                    }

                    echo generate_link($text, $vars, array(
                        'state' => $option
                    ));
                    if ($vars['state'] == $option) {
                        echo '</span>';
                    }

                    $sep = ' | ';
                }

                unset($sep);
                echo '</div>';
                echo '</div>';
                echo '<div style="margin:10px 10px 0px 10px;" id="message"></div>';
                echo '<div class="panel-body">';

                $sql_param = array();

                if (isset($vars['state'])) {
                    if ($vars['state'] == 'ok') {
                        $state = '0';
                    } elseif ($vars['state'] == 'critical') {
                        $state = '2';
                    } elseif ($vars['state'] == 'warning') {
                        $state = '1';
                    }
                }

                if (isset($state)) {
                    $where .= " AND service_status= ? AND service_disabled='0' AND `service_ignore`='0'";
                    $sql_param[] = $state;
                }

                if (Auth::user()->hasGlobalRead()) {
                    $host_sql = 'SELECT `D`.`device_id`,`D`.`hostname`,`D`.`sysName` FROM devices AS D, services AS S WHERE D.device_id = S.device_id GROUP BY `D`.`hostname`, `D`.`device_id`, `D`.`sysName` ORDER BY D.hostname';
                    $host_par = array();
                } else {
                    $host_sql = 'SELECT `D`.`device_id`,`D`.`hostname`,`D`.`sysName` FROM devices AS D, services AS S, devices_perms AS P WHERE D.device_id = S.device_id AND D.device_id = P.device_id AND P.user_id = ? GROUP BY `D`.`hostname`, `D`.`device_id`, `D`.`sysName` ORDER BY D.hostname';
                    $host_par = array(Auth::id());
                }

                $shift = 1;
                foreach (dbFetchRows($host_sql, $host_par) as $device) {
                    $device_id = $device['device_id'];
                    $device_hostname = $device['hostname'];
                    $device_sysName = $device['sysName'];
                    $devlink = generate_device_link($device, null, array('tab' => 'services'));
                    if ($shift == 1) {
                        array_unshift($sql_param, $device_id);
                        $shift = 0;
                    } else {
                        $sql_param[0] = $device_id;
                    }

                    $header = true;
                    $footer = false;

                    $service_iteration = 0;
                    $services = dbFetchRows("SELECT * FROM `services` WHERE `device_id` = ? $where ORDER BY service_type", $sql_param);
                    $services_count = count($services);
                    foreach ($services as $service) {
                        if ($service['service_status'] == '2') {
                            $label = 'label-danger';
                            $title = 'CRITICAL';
                        } elseif ($service['service_status'] == '1') {
                            $label = 'label-warning';
                            $title = 'WARNING';
                        } elseif ($service['service_status'] == '0') {
                            $label = 'label-success';
                            $title = 'OK';
                        } else {
                            $label = 'label-info';
                            $title = 'UNKNOWN';
                        }

                        $service_iteration++;

                        if ($service_iteration < 2 && $header) {
                            echo '<div class="panel panel-default">';
                            echo '<div class="panel-heading"><h3 class="panel-title">' . $devlink . '</h3>' . $device_sysName . '</div>';
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

                        echo '<tr id="row_' . $service['service_id'] . '">';
                        echo '<td><span data-toggle="tooltip" title="' . $title . '" class="alert-status ' . $label . '"></span></td>';
                        echo '<td>' . nl2br(display($service['service_type'])) . '</td>';
                        echo '<td>' . formatUptime(time() - $service['service_changed']) . '</td>';
                        echo '<td>' . nl2br(display($service['service_desc'])) . '</td>';
                        echo '<td>' . nl2br(display($service['service_message'])) . '</td>';

                        if (Auth::user()->hasGlobalAdmin()) {
                            echo "<td>
                                    <button type='button' class='btn btn-primary btn-sm' aria-label='Edit' data-toggle='modal' data-target='#create-service' data-service_id='{$service['service_id']}' name='edit-service'><i class='fa fa-pencil' aria-hidden='true'></i></button>
                                    <button type='button' class='btn btn-danger btn-sm' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-service_id='{$service['service_id']}' name='delete-service'><i class='fa fa-trash' aria-hidden='true'></i></button>
                                    </td>";
                        }
                        echo '</tr>';

                        if ($service_iteration >= $services_count) {
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
