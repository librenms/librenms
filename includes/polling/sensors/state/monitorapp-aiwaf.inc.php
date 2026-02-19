<?php

$process_prefix = 'process.';

if (str_starts_with((string) $sensor['sensor_index'], $process_prefix)) {
    static $process_status = null;

    if ($process_status === null) {
        $process_status = [];
        foreach (preg_split('/\s*,\s*/', trim((string) $sensor_value)) as $entry) {
            if ($entry === '') {
                continue;
            }

            if (! preg_match('/^(.*?)\(([^)]+)\)$/', $entry, $matches)) {
                continue;
            }

            $name = trim($matches[1]);
            $status = strtoupper(trim($matches[2]));
            if ($name === '') {
                continue;
            }

            $key = strtolower((string) preg_replace('/[^A-Za-z0-9._-]+/', '_', $name));
            if ($key === '') {
                continue;
            }

            $process_status[$key] = $status === 'OK' ? 1 : 2;
        }

        if (empty($process_status)) {
            $process_status = false;
        }
    }

    if ($process_status === false) {
        return;
    }

    $key = substr((string) $sensor['sensor_index'], strlen($process_prefix));
    if (isset($process_status[$key])) {
        $sensor_value = $process_status[$key];
    } else {
        $sensor_value = 2;
    }
}
