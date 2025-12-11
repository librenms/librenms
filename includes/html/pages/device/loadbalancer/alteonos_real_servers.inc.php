<?php

require_once __DIR__ . '/alteonos_common.inc.php';

$rows = alteonos_loadbalancer_fetch($device, 'alteonos_real_servers');
alteonos_render_sensor_table(__('Alteon Real Server Status'), $rows);
