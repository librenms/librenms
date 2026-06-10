<?php

echo '<tr>';
echo '<td>';
echo $mef['mefIdent'];

echo '</td>';
echo '<td>' . $mef['mefType'] . '</td>';
echo '<td>' . $mef['mefMTU'] . '</td>';

echo '<td>';
if ($mef['mefAdmState'] == 'unlocked') {
    echo '<i class="fa fa-unlock fa-lg icon-theme" aria-hidden="true" style="color:green"></i>';
} else {
    echo '<i class="fa fa-lock fa-lg icon-theme" aria-hidden="true" style="color:red"></i>';
}
echo '</td>';
if ($mef['mefRowState'] == 'active') {
    echo '<td><span style="min-width:40px; display:inline-block;" class="label label-success">active</span></td>';
} else {
    echo '<td><span style="min-width:40px; display:inline-block;" class="label label-default">' . $mef['mefRowState'] . '</span></td>';
}

echo '</tr>';
