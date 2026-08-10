<?php

namespace LibreNMS\DB\Schema\Adapters;

interface SchemaAdapter
{
    /**
     * Set up the database session state (e.g., time zone).
     */
    public function setSessionState(): void;

    /**
     * Get SQL statements to prepend to a list of queries (e.g., SET TIME_ZONE).
     *
     * @param  string[]  $queries
     * @return string[]
     */
    public function getPreSql(array $queries): array;

    /**
     * Get the name of the schema/database to use for filtering tables.
     */
    public function getSchemaName(): string;

    /**
     * Fetch platform-specific extra information for columns (e.g., MySQL's EXTRA).
     *
     * @param  array<int, array{name: string}>  $tables
     * @return array<string, array<string, mixed>>
     */
    public function fetchExtras(array $tables): array;

    /**
     * Map a platform-specific column definition to the generic schema format.
     *
     * @param  array<string, mixed>  $col
     * @param  array<string, mixed>  $tableExtras
     * @return array{Field: string, Type: string, Null: bool, Default?: mixed, Extra: string}
     */
    public function mapColumn(array $col, array $tableExtras): array;

    /**
     * Compare a master column definition with the current one.
     *
     * @param  array<string, mixed>  $master
     * @param  array<string, mixed>  $current
     */
    public function columnsMatch(array $master, array $current): bool;

    /**
     * Compare a master index definition with the current one.
     *
     * @param  array<string, mixed>  $master
     * @param  array<string, mixed>  $current
     */
    public function indexesMatch(array $master, array $current): bool;

    /**
     * Compare a master constraint definition with the current one.
     *
     * @param  array<string, mixed>  $master
     * @param  array<string, mixed>  $current
     */
    public function constraintsMatch(array $master, array $current): bool;

    /**
     * Generate platform-specific SQL to add a foreign key constraint.
     *
     * @param  array<string, mixed>  $c
     * @return string[]
     */
    public function addConstraintSql(string $table, array $c): array;

    /**
     * Generate platform-specific SQL to drop a foreign key constraint.
     *
     * @return string[]
     */
    public function dropConstraintSql(string $table, string $name): array;
}
