<?php
require_once 'includes/modal/new_alert_map.inc.php';
require_once 'includes/modal/delete_alert_map.inc.php';

$no_refresh = true;

echo '<div class="row"><div class="col-sm-12"><span id="message"></span></div></div>';
echo '<div class="table-responsive">';
echo '<table class="table table-condensed table-hover"><thead><tr>';
echo '<th>Rule</th><th>Target</th><th>Actions</th>';
echo '</tr></thead><tbody>';
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
        echo "<button type='button' class='btn btn-primary btn-sm' aria-label='Edit' data-toggle='modal' data-target='#create-map' data-map_id='".$link['id']."' name='edit-alert-map'><span class='glyphicon glyphicon-pencil' aria-hidden='true'></span></button> ";
        echo "<button type='button' class='btn btn-danger btn-sm' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-map_id='".$link['id']."' name='delete-alert-map'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button>";
    echo '</td>';
    echo '</tr>';
}

echo '</tbody></table></div>';
echo "<button type='button' class='btn btn-primary btn-sm' aria-label='Add' data-toggle='modal' data-target='#create-map' data-map_id='' name='create-alert-map'>Create new Map</button> ";
