<?php

echo '<table cellspacing="0" cellpadding="5" width="100%">';

$i = 0;
foreach (dbFetchRows('SELECT * FROM `packages` WHERE `device_id` = ? ORDER BY `name`', [$device['device_id']]) as $entry) {
    echo '<tr class="list">';
    echo '<td width=200><a href="' . \LibreNMS\Util\Url::generate($vars, ['name' => $entry['name']]) . '">' . $entry['name'] . '</a></td>';
    if ($build != '') {
        $dbuild = '-' . $entry['build'];
    } else {
        $dbuild = '';
    }

    echo '<td>' . $entry['version'] . $dbuild . '</td>';
    echo '<td>' . $entry['arch'] . '</td>';
    echo '<td>' . $entry['manager'] . '</td>';
    echo '<td>' . \LibreNMS\Util\Number::formatSi($entry['size'], 2, 3, '') . '</td>';
    echo '</tr>';

    $i++;
}

echo '</table>';
