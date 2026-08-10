<?php

namespace LibreNMS\DB\Schema\Adapters;

class SqliteAdapter extends BaseAdapter
{
    public function getSchemaName(): string
    {
        return 'main';
    }

    /**
     * @param  array<string, mixed>  $master
     * @param  array<string, mixed>  $current
     */
    public function columnsMatch(array $master, array $current): bool
    {
        $masterType = strtolower((string) $master['Type']);
        $currentType = strtolower((string) $current['Type']);

        // Normalize SQLite types to MySQL types for comparison
        /** @var array<string, string[]> $typeMap */
        $typeMap = [
            'integer' => ['int', 'tinyint', 'smallint', 'mediumint', 'bigint', 'integer'],
            'text' => ['text', 'mediumtext', 'longtext', 'blob', 'mediumblob', 'longblob', 'json', 'varbinary', 'binary'],
            'blob' => ['text', 'mediumtext', 'longtext', 'blob', 'mediumblob', 'longblob', 'varbinary', 'binary'],
            'varchar' => ['varchar', 'string', 'char', 'enum', 'int'],
            'datetime' => ['datetime', 'timestamp'],
            'numeric' => ['decimal', 'numeric', 'double', 'float'],
            'float' => ['double', 'float', 'decimal', 'numeric'],
        ];

        $masterClean = preg_replace('/\(.*\)/', '', str_replace(' unsigned', '', $masterType));
        $currentClean = preg_replace('/\(.*\)/', '', strtolower((string) $currentType));

        $typeMatch = false;
        if ($masterClean === $currentClean) {
            $typeMatch = true;
        } else {
            foreach ($typeMap as $sqlite => $mysqls) {
                if ($currentClean === $sqlite && in_array($masterClean, $mysqls)) {
                    $typeMatch = true;
                    break;
                }
            }
        }

        // Handle ENUM vs VARCHAR/TEXT
        if (! $typeMatch && str_starts_with((string) $masterClean, 'enum')) {
            $typeMatch = in_array($currentClean, ['varchar', 'text', 'string']);
        }

        // Check if types match
        if (! $typeMatch) {
            return false;
        }

        // Check nullability
        if ((bool) $master['Null'] !== (bool) $current['Null']) {
            // SQLite sometimes differs in nullability for certain columns (especially BLOBs) due to migration quirks
            if ($currentClean !== 'blob') {
                return false;
            }
        }

        // Check Extra (auto_increment, on update CURRENT_TIMESTAMP)
        $masterExtra = (string) $master['Extra'];
        $currentExtra = (string) $current['Extra'];

        if ($masterExtra === $currentExtra) {
            $extraMatch = true;
        } else {
            // Ignore auto_increment on SQLite integers
            if ($masterExtra === 'auto_increment' && $currentClean === 'integer') {
                $extraMatch = true;
            } elseif ($masterExtra === 'on update CURRENT_TIMESTAMP' && $currentExtra === '') {
                // SQLite doesn't support this, so we ignore the mismatch
                $extraMatch = true;
            } elseif (str_contains($masterExtra, 'GENERATED') && $currentExtra === '') {
                // Generated columns are not always reported in Extra for SQLite
                $extraMatch = true;
            } else {
                $extraMatch = false;
            }
        }

        if (! $extraMatch) {
            return false;
        }

        // Check Default
        $masterDefault = $master['Default'] ?? null;
        $currentDefault = $current['Default'] ?? null;

        if ($masterDefault == $currentDefault) {
            return true;
        }

        // Normalize string vs numeric defaults (SQLite might report 0 instead of '0')
        if (is_numeric($masterDefault) && is_numeric($currentDefault) && (float) $masterDefault == (float) $currentDefault) {
            return true;
        }

        // SQLite sometimes loses defaults during complex migrations
        if (($masterDefault === '0' || $masterDefault === 0) && $currentDefault === null) {
            return true;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $master
     * @param  array<string, mixed>  $current
     */
    public function indexesMatch(array $master, array $current): bool
    {
        return array_map(strtolower(...), $master['Columns']) === array_map(strtolower(...), $current['Columns']) &&
            $master['Unique'] == $current['Unique'];
    }

    /**
     * @param  array<string, mixed>  $master
     * @param  array<string, mixed>  $current
     */
    public function constraintsMatch(array $master, array $current): bool
    {
        return $master['foreign_key'] === $current['foreign_key'] &&
            $master['table'] === $current['table'] &&
            $master['key'] === $current['key'] &&
            $master['extra'] === $current['extra'];
    }

    public function dropConstraintSql(string $table, string $name): array
    {
        return ["-- SQLite does not support dropping foreign keys by name ($table/$name)"];
    }

    /**
     * @param  array<string, mixed>  $c
     * @return string[]
     */
    public function addConstraintSql(string $table, array $c): array
    {
        return ["-- SQLite does not support adding foreign keys to existing tables ($table/{$c['name']})"];
    }
}
