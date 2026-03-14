<?php

/**
 * avtech.inc.php
 *
 * LibreNMS temperature discovery for AVTECH Room Alert devices
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS Contributors
 */
if (isset($pre_cache['ramax-channels'])) {
    foreach ($pre_cache['ramax-channels'] as $index => $channel) {
        $type = $channel[3] ?? '';
        $value = $channel[4] ?? null;
        $descr = $channel['label'] ?? 'Temperature';

        if ($type === 'Temperature' && $value !== null) {
            $oid = '.1.3.6.1.4.1.20916.1.14.3.1.1.4.' . $index;
            $sensor_index = 'ramax-' . md5((string) $index);
            discover_sensor(
                null, 'temperature', $device,
                $oid, $sensor_index, 'avtech',
                $descr, 100, 1,
                null, null, null, null,
                $value
            );
        }
    }
}
