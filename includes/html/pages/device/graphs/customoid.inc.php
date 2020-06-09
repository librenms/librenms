<?php

$row = 1;

foreach (dbFetchRows('SELECT * FROM `customoids` WHERE `device_id` = ? ORDER BY `customoid_descr`', array($device['device_id'])) as $customoid) {
    if (!is_integer($row / 2)) {
        $row_colour = Config::get("list_colour.even");
    } else {
        $row_colour = Config::get("list_colour.odd");
    }
    $customoid_descr     = $customoid['customoid_descr'];
    $customoid_unit      = $customoid['customoid_unit'];
    $customoid_current   = format_si($customoid['customoid_current']).$customoid_unit;
    $customoid_limit     = format_si($customoid['customoid_limit']).$customoid_unit;
    $customoid_limit_low = format_si($customoid['$customoid_limit_low']).$customoid_unit;
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$customoid_descr <div class='pull-right'>$customoid_current | $customoid_limit_low <> $customoid_limit</div></h3>
            </div>";
    echo "<div class='panel-body'>";

    $graph_array['id']    = $customoid['customoid_id'];
    $graph_array['title'] = $customoid['customoid_descr'];
    $graph_array['type']  = 'customoid';

    include 'includes/html/print-graphrow.inc.php';

    echo '</div></div>';

    $row++;
}
