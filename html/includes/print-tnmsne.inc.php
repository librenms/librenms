<?php
echo '<tr class="list">';
echo '<td class="list">';
echo $tnmsne['neName'];
echo '</td>';
echo '<td class="list">'. $tnmsne['neLocation'] . '</td>';
echo '<td class="list">'. $tnmsne['neType'] . '</td>';

if ($tnmsne['neOpMode'] == 'operation') {
    echo '<td class="list"><span style="min-width:40px; display:inline-block;" class="label label-success">operation</span></td>';
} else {
    echo '<td class="list"><span style="min-width:40px; display:inline-block;" class="label label-danger">'.$tnmsne['neOpMode'].'</span></td>';
}
echo '<td class="list">';
switch ($tnmsne['neAlarm']) {
    case "cleared":
        echo '<span style="min-width:40px; display:inline-block;" class="label label-success">cleared</span>';
        break;
    case "warning":
        echo '<span style="min-width:40px; display:inline-block;" class="label label-warning">warning</span>';
        break;
    case "minor":
    case "major":
    case "critical":
    case "indeterminate":
        echo '<span style="min-width:40px; display:inline-block;" class="label label-danger">' . $tnmsne['neAlarm'] . '</span>';
        break;
    default:
        echo '<span style="min-width:40px; display:inline-block;" class="label label-default">' . $tnmsne['neAlarm'] . '</span>';
}
echo '</td>';
if ($tnmsne['neOpState'] == 'enabled') {
    echo '<td class="list"><span style="min-width:40px; display:inline-block;" class="label label-success">enabled</span></td>';
} else {
    echo '<td class="list"><span style="min-width:40px; display:inline-block;" class="label label-danger">'.$tnmsne['neOpState'].'</span></td>';
}
echo '</tr>';
