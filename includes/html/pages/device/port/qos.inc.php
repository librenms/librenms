<?php

echo view('device.tabs.ports.qos', [
    'port' => $port,
    'show' => $vars['show'],
]);
