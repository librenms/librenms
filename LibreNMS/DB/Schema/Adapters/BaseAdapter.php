<?php

namespace LibreNMS\DB\Schema\Adapters;

use Illuminate\Database\Connection;

abstract class BaseAdapter implements SchemaAdapter
{
    public function __construct(protected Connection $db)
    {
    }

    public function setSessionState(): void
    {
    }

    public function getPreSql(array $queries): array
    {
        return [];
    }

    abstract public function getSchemaName(): string;

    public function fetchExtras(array $tables): array
    {
        return [];
    }

    public function mapColumn(array $col, array $tableExtras): array
    {
        $def = [
            'Field' => $col['name'],
            'Type' => (string) $col['type'],
            'Null' => (bool) $col['nullable'],
            'Extra' => '',
        ];

        if (isset($col['default']) && strtoupper((string) $col['default']) !== 'NULL') {
            $default = (string) $col['default'];
            if (str_starts_with($default, "'") && str_ends_with($default, "'")) {
                $default = substr($default, 1, -1);
            }
            $def['Default'] = str_contains(strtolower($default), 'current_timestamp') ? 'CURRENT_TIMESTAMP' : $default;
        }

        return $def;
    }

    public function columnsMatch(array $master, array $current): bool
    {
        return $master['Type'] === $current['Type'] &&
            $master['Null'] == $current['Null'] &&
            ($master['Default'] ?? null) == ($current['Default'] ?? null) &&
            $master['Extra'] == $current['Extra'];
    }

    public function indexesMatch(array $master, array $current): bool
    {
        return $master['Name'] === $current['Name'] &&
            array_map(strtolower(...), $master['Columns']) === array_map(strtolower(...), $current['Columns']) &&
            $master['Unique'] == $current['Unique'];
    }

    public function constraintsMatch(array $master, array $current): bool
    {
        return $master === $current;
    }

    public function addConstraintSql(string $table, array $c): string
    {
        return "-- addConstraintSql not implemented for this driver";
    }

    public function dropConstraintSql(string $table, string $name): string
    {
        return "-- dropConstraintSql not implemented for this driver";
    }
}
