<?php

$row = 1;

foreach (dbFetchRows('SELECT * FROM `customoids` WHERE `device_id` = ? ORDER BY `customoid_descr`', [$device['device_id']]) as $customoid) {
    if (! is_integer($row / 2)) {
        $row_colour = Config::get('list_colour.even');
    } else {
        $row_colour = Config::get('list_colour.odd');
    }
    $customoid_descr = $customoid['customoid_descr'];
    $customoid_unit = $customoid['customoid_unit'];
    $customoid_current = \LibreNMS\Util\Number::formatSi($customoid['customoid_current'], 2, 3, '') . $customoid_unit;
    $customoid_limit = \LibreNMS\Util\Number::formatSi($customoid['customoid_limit'], 2, 3, '') . $customoid_unit;
    $customoid_limit_low = \LibreNMS\Util\Number::formatSi($customoid['$customoid_limit_low'], 2, 3, '') . $customoid_unit;
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$customoid_descr <div class='pull-right'>$customoid_current | $customoid_limit_low <> $customoid_limit</div></h3>
            </div>";
    echo "<div class='panel-body'>";

    $graph_array['id'] = $customoid['customoid_id'];
    $graph_array['title'] = $customoid['customoid_descr'];
    $graph_array['type'] = 'customoid';

    include 'includes/html/print-graphrow.inc.php';

    echo '</div></div>';

    $row++;
}
