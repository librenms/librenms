<?php

echo view('device.tabs.ports.transceivers', [
    'data' => ['transceivers' => $port->transceivers],
]);
