<?php

// The port eventlog is now a standalone page (route: port.events);
// this shim keeps legacy ?view=events URLs working.
$url = route('port.events', ['port' => $port->port_id]);
echo '<meta http-equiv="refresh" content="0;url=' . e($url) . '">';
echo '<p>Eventlog: <a href="' . e($url) . '">' . e($url) . '</a></p>';

$pagetitle[] = 'Events';
