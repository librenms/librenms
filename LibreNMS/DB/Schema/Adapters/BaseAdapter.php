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

    /**
     * @param  array<int, array{name: string}>  $tables
     * @return array<string, array<string, mixed>>
     */
    public function fetchExtras(array $tables): array
    {
        return [];
    }

    /**
     * @param  array<string, mixed>  $col
     * @param  array<string, mixed>  $tableExtras
     * @return array{Field: string, Type: string, Null: bool, Default?: mixed, Extra: string}
     */
    public function mapColumn(array $col, array $tableExtras): array
    {
        $def = [
            'Field' => $col['name'],
            'Type' => (string) $col['type'],
            'Null' => (bool) $col['nullable'],
            'Extra' => '',
        ];

        if (isset($col['default']) && strtoupper((string) $col['default']) !== 'NULL') {
            $default = trim((string) $col['default'], "'");
            $def['Default'] = str_contains(strtolower($default), 'current_timestamp') ? 'CURRENT_TIMESTAMP' : $default;
        }

        return $def;
    }

    /**
     * @param  array<string, mixed>  $master
     * @param  array<string, mixed>  $current
     */
    public function columnsMatch(array $master, array $current): bool
    {
        return $master['Type'] === $current['Type'] &&
            $master['Null'] == $current['Null'] &&
            ($master['Default'] ?? null) == ($current['Default'] ?? null) &&
            $master['Extra'] == $current['Extra'];
    }

    /**
     * @param  array<string, mixed>  $master
     * @param  array<string, mixed>  $current
     */
    public function indexesMatch(array $master, array $current): bool
    {
        return $master['Name'] === $current['Name'] &&
            array_map(strtolower(...), $master['Columns']) === array_map(strtolower(...), $current['Columns']) &&
            $master['Unique'] == $current['Unique'];
    }

    /**
     * @param  array<string, mixed>  $master
     * @param  array<string, mixed>  $current
     */
    public function constraintsMatch(array $master, array $current): bool
    {
        return $master === $current;
    }

    /**
     * @param  array<string, mixed>  $c
     * @return string[]
     */
    public function addConstraintSql(string $table, array $c): array
    {
        return ['-- addConstraintSql not implemented for this driver'];
    }

    public function dropConstraintSql(string $table, string $name): array
    {
        return ['-- dropConstraintSql not implemented for this driver'];
    }
}
