<?php

$unitlen = strlen($unit_text);

$polling_type = 'app';
$colours = 'psychedelic';

$rrd_filename = Rrd::name($device['hostname'], [
    $polling_type,
    $name,
    $app->app_id,
    $rrd_identifier,
]);
