<?php

require_once __DIR__ . '/alteonos_common.inc.php';

$rows = alteonos_loadbalancer_fetch($device, 'alteonos_virtual_services');
alteonos_render_sensor_table(__('Alteon Virtual Service Status'), $rows);
