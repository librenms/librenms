<?php

echo view('device.tabs.ports.qos', [
    'device' => $device,
    'port_id' => $port->port_id,
    'port_ifindex' => $port->ifIndex,
]);
