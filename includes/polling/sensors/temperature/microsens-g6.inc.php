<?php

if (!empty($sensor_value) && is_string($sensor_value)) {
    if (preg_match('/([0-9.-]+)\s*C/i', $sensor_value, $matches)) {
        $sensor_value = (float)$matches[1];
    }
}
