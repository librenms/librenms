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
     * @param string[] $queries
     * @return string[]
     */
    public function getPreSql(array $queries): array;

    /**
     * Get the name of the schema/database to use for filtering tables.
     */
    public function getSchemaName(): string;

    /**
     * Fetch platform-specific extra information for columns (e.g., MySQL's EXTRA).
     */
    public function fetchExtras(array $tables): array;

    /**
     * Map a platform-specific column definition to the generic schema format.
     */
    public function mapColumn(array $col, array $tableExtras): array;

    /**
     * Compare a master column definition with the current one.
     */
    public function columnsMatch(array $master, array $current): bool;

    /**
     * Compare a master index definition with the current one.
     */
    public function indexesMatch(array $master, array $current): bool;

    /**
     * Compare a master constraint definition with the current one.
     */
    public function constraintsMatch(array $master, array $current): bool;

    /**
     * Generate platform-specific SQL to add a foreign key constraint.
     */
    public function addConstraintSql(string $table, array $c): string;

    /**
     * Generate platform-specific SQL to drop a foreign key constraint.
     */
    public function dropConstraintSql(string $table, string $name): string;
}
