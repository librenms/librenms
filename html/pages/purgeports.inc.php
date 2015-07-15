<?php

foreach (dbFetchRows("SELECT * FROM `ports` WHERE `deleted` = '1'") as $port) {
    echo "<div style='font-weight: bold;'>Deleting port ".$port['port_id'].' - '.$port['ifDescr'];
    delete_port($port['port_id']);
    echo '</div>';
}
