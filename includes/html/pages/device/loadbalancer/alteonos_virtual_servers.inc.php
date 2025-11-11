<?php

require_once __DIR__ . '/alteonos_common.inc.php';

$rows = alteonos_loadbalancer_fetch($device, 'alteonos_virtual_servers');

foreach ($rows as &$row) {
    if (! empty($row['sensor_descr'])) {
        $row['sensor_descr'] = preg_replace('/^SLB\s+/i', '', $row['sensor_descr']);
    }
}
unset($row);

alteonos_render_sensor_table(__('Alteon Virtual Server Status'), $rows);
