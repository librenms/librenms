<?php

unset($datas);
$datas[] = 'list';
$datas[] = 'map';

$page_text['list'] = 'List';
$page_text['map'] = 'Map';

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'neighbours',
];

print_optionbar_start();
echo "<span style='font-weight: bold;'>Neighbours</span> &#187; ";

$selection = basename($vars['selection'] ?? 'list');

unset($sep);
foreach ($datas as $type) {
    echo $sep;

    if ($selection == $type) {
        echo '<span class="pagemenu-selected">';
    }
    echo generate_link($page_text[$type], $link_array, [
        'selection' => $type,
    ]);
    if ($selection == $type) {
        echo '</span>';
    }
    $sep = ' | ';
}
print_optionbar_end();
include "includes/html/pages/device/neighbours/$selection.inc.php";
$pagetitle[] = 'Neighbours';
