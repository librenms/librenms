<?php

if ($device['os_group'] == 'printer') {
    if ($sensor['sensor_type'] === 'hrPrinterDetectedErrorState') {
        $printer_states =
        [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'No issues'],
            ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Paper Low'],
            ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'No Paper'],
            ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'Toner Low'],
            ['value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'No Toner'],
            ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'Door Open'],
            ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'Jammed'],
            ['value' => 7, 'generic' => 2, 'graph' => 0, 'descr' => 'Offline'],
            ['value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'Service Needed'],
            ['value' => 9, 'generic' => 1, 'graph' => 0, 'descr' => 'Warning, multiple issues'],
            ['value' => 10, 'generic' => 2, 'graph' => 0, 'descr' => 'Critical, multiple issues'],
        ];
        $bit_flags = q_bridge_bits2indices($sensor_value);
        $is_critical = false;
        if (count($bit_flags) == 0) {
            $sensor_value = 0;
        } else {
            for ($i = 0; $i < count($bit_flags); $i++) {
                if ($bit_flags[$i] > 8) {
                    continue;
                }
                $sensor_value = $printer_states[$bit_flags[$i]]['value'];
                if ($printer_states[$bit_flags[$i]]['generic'] == 2) {
                    $is_critical = true;
                    break;
                }
            }
            // cannot create an index for each bit combination, instead warning or critical
            if (count($bit_flags) > 1) {
                // multiple issues, check above list
                $sensor_value = $is_critical ? 10 : 9;
            }
        }
        d_echo('Polling hrPrinterDetectedErrorState: ' . $sensor_value);
    }
}
