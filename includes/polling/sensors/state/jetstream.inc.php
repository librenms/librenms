<?php
if ($device['os'] == 'jetstream') {
    if (in_array($sensor['sensor_type'], ['ddmStatusDataReady', 'ddmStatusLossSignal', 'ddmStatusTxFault'])) {
        if ($sensor_value == 'True') {
            $sensor_value = 1;
        } elseif ($sensor_value == 'False') {
            $sensor_value = 0;
        } else {
            $sensor_value = null;
        }
    }
}
