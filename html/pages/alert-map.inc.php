<?php
require_once 'includes/modal/new_alert_map.inc.php';
require_once 'includes/modal/delete_alert_map.inc.php';

$no_refresh = true;

echo '<div class="panel panel-default panel-condensed">';
echo '<div class="panel-heading">';
echo '<strong>Rule mapping</strong>';
echo '<div class="pull-right">';
echo '<span style="font-weight:bold;">Actions &#187;&nbsp;</span>';
echo '<a href="#" data-toggle="modal" data-target="#create-map" data-map_id="" name="create-alert-map">Create new Map</a>';
echo '</div>';
echo '</div>';
echo '<div class="panel-body">';
echo '<div style="margin:10px 10px 0px 10px;" id="message"></div>';

echo '<div class="table-responsive">';
echo '<table class="table table-condensed table-hover table-striped">';
echo '<thead>';
echo '<th>Rule</th>';
echo '<th>Target</th>';
echo '<th>Actions</th>';
echo '</thead>';
echo '<tbody>';

foreach (dbFetchRows('SELECT alert_map.target,alert_map.id,alert_rules.name FROM alert_map,alert_rules WHERE alert_map.rule=alert_rules.id ORDER BY alert_map.rule ASC') as $link) {
    if ($link['target'][0] == 'g') {
        $link['target'] = substr($link['target'], 1);
        $link['target'] = '<a href="'.generate_url(array('page' => 'devices', 'group' => $link['target'])).'">'.ucfirst(dbFetchCell('SELECT name FROM device_groups WHERE id = ?', array($link['target']))).'</a>';
    } elseif (is_numeric($link['target'])) {
        $link['target'] = '<a href="'.generate_url(array('page' => 'device', 'device' => $link['target'])).'">'.dbFetchCell('SELECT hostname FROM devices WHERE device_id = ?', array($link['target'])).'</a>';
    }

    echo '<tr id="row_'.$link['id'].'">';
    echo '<td>'.$link['name'].'</td>';
    echo '<td>'.$link['target'].'</td>';
    echo '<td>';
        echo "<button type='button' class='btn btn-primary btn-sm' aria-label='Edit' data-toggle='modal' data-target='#create-map' data-map_id='".$link['id']."' name='edit-alert-map'><i class='fa fa-pencil' aria-hidden='true'></i></button> ";
        echo "<button type='button' class='btn btn-danger btn-sm' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-map_id='".$link['id']."' name='delete-alert-map'><span class='fa fa-trash' aria-hidden='true'></i></button>";
    echo '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</div>';
echo '</div>';
