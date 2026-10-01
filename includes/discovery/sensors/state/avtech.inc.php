<?php

/**
 * avtech.inc.php
 *
 * LibreNMS state discovery for AVTECH Room Alert devices
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS Contributors
 */
if (isset($pre_cache['ramax-channels'])) {
    foreach ($pre_cache['ramax-channels'] as $index => $channel) {
        $type = $channel[3] ?? '';
        $value = $channel[4] ?? null;
        $descr = $channel['label'] ?? 'Switch';
        if ($type === 'Switch' && $value !== null) {
            $oid = '.1.3.6.1.4.1.20916.1.14.3.1.1.4.' . $index;
            $sensor_index = 'ramax-' . md5((string) $index);
            $states = [
                ['value' => 0, 'generic' => -1, 'descr' => 'Off'],
                ['value' => 1, 'generic' => 0,  'descr' => 'On'],
            ];
            create_state_index('avtech-switch', $states);
            discover_sensor(
                null, 'state', $device,
                $oid, $sensor_index, 'avtech-switch',
                $descr, 1, 1,
                null, null, null, null,
                $value
            );
        }
    }
}
