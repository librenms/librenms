<?php

namespace LibreNMS\DB\Schema\Adapters;

class SqliteAdapter extends BaseAdapter
{
    public function getSchemaName(): string
    {
        return 'main';
    }

    public function columnsMatch(array $master, array $current): bool
    {
        $masterType = strtolower((string) $master['Type']);
        $currentType = strtolower((string) $current['Type']);

        // Normalize SQLite types to MySQL types for comparison
        $typeMap = [
            'integer' => 'int',
            'text' => 'blob',
        ];

        $masterClean = preg_replace('/\(.*\)/', '', str_replace(' unsigned', '', $masterType));
        $currentClean = $typeMap[$currentType] ?? $currentType;

        $typeMatch = ($masterClean === $currentClean) ||
            ($masterClean === 'json' && in_array($currentClean, ['json', 'longtext', 'text', 'varchar']));

        return $typeMatch && $master['Null'] == $current['Null'];
    }

    public function dropConstraintSql(string $table, string $name): string
    {
        return "-- SQLite does not support dropping foreign keys by name ($table/$name)";
    }

    public function addConstraintSql(string $table, array $c): string
    {
        return "-- SQLite does not support adding foreign keys to existing tables ($table/{$c['name']})";
    }
}
