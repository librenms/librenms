<?php

list($devices, $d_more) = include 'devices.inc.php';
list($groups, $g_more) = include 'groups.inc.php';

$groups = array_map(function($group) {
    $group['id'] = 'g' . $group['id'];
    return $group;
}, $groups);

$data = [
    ['text' => 'Devices', 'children' => $devices],
    ['text' => 'Groups', 'children' => $groups]
];

return [$data, $d_more || $g_more];
