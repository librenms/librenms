<?php

namespace LibreNMS\Alert;

use InvalidArgumentException;

class KeyValueOptions
{
    /**
     * @param  array<array-key, mixed>  $rows
     * @return array<string, string>
     */
    public static function fromRows(array $rows): array
    {
        $options = [];
        foreach ($rows as $row) {
            if (! is_array($row) || ! is_string($row['name'] ?? null) || ! is_string($row['value'] ?? null)) {
                throw new InvalidArgumentException('Each entry must have a string name and value.');
            }
            $name = trim($row['name']);
            if ($name === '' && $row['value'] === '') {
                continue;
            }
            if ($name === '') {
                throw new InvalidArgumentException('Each entry with a value must have a name.');
            }
            if (array_key_exists($name, $options)) {
                throw new InvalidArgumentException('Entry names must be unique.');
            }
            $options[$name] = $row['value'];
        }

        return $options;
    }

    /**
     * @param  array<array-key, mixed>|string|null  $config
     * @return array<string, string>
     */
    public static function fromConfig(array|string|null $config): array
    {
        if (is_array($config)) {
            foreach ($config as $value) {
                if (! is_string($value)) {
                    throw new InvalidArgumentException('Entry values must be strings.');
                }
            }

            return $config;
        }

        $options = [];
        foreach (preg_split('/\r\n|\r|\n/', $config ?? '', -1, PREG_SPLIT_NO_EMPTY) as $line) {
            if (str_contains($line, '=')) {
                [$name, $value] = explode('=', $line, 2);
                $options[$name] = trim($value);
            }
        }

        return $options;
    }
}
