<?php
namespace InfluxDB;

function list_to_string(array $elements, $escape = false)
{
    array_walk($elements, function(&$value, $key) use ($escape) {
        if ($escape && is_string($value)) {
            $value = "\"{$value}\"";
        }

        $value = "{$key}={$value}";
    });

    return implode(",", $elements);
}
