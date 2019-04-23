<?php

echo '<table cellspacing="0" cellpadding="5" width="100%">';

$i = 0;
foreach (dbFetchRows('SELECT * FROM `packages` WHERE `device_id` = ? ORDER BY `name`', array($device['device_id'])) as $entry) {
    echo '<tr class="list">';
    echo '<td width=200><a href="'.generate_url($vars, array('name' => $entry['name'])).'">'.$entry['name'].'</a></td>';
    if ($build != '') {
        $dbuild = '-'.$entry['build'];
    } else {
        $dbuild = '';
    }

    echo '<td>'.$entry['version'].$dbuild.'</td>';
    echo '<td>'.$entry['arch'].'</td>';
    echo '<td>'.$entry['manager'].'</td>';
    echo '<td>'.format_si($entry['size']).'</td>';
    echo '</tr>';

    $i++;
}

echo '</table>';
