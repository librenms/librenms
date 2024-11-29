<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->migrateCharsetTo('utf8mb4', 'utf8mb4_unicode_ci');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $this->migrateCharsetTo('utf8', 'utf8_unicode_ci');
    }

    protected function migrateCharsetTo($charset, $collation)
    {
        if (\LibreNMS\DB\Eloquent::getDriver() != 'mysql') {
            return;
        }

        $databaseName = DB::connection()->getDatabaseName();

        // Change default charset and collation
        DB::unprepared("ALTER SCHEMA `{$databaseName}` DEFAULT CHARACTER SET {$charset} DEFAULT COLLATE {$collation};");

        // Get the list of all tables
        $tableNames = DB::table('information_schema.tables')
            ->where('table_schema', $databaseName)
            ->where('TABLE_TYPE', 'BASE TABLE')
            ->pluck('TABLE_NAME');

        // Iterate through the list and alter each table
        foreach ($tableNames as $tableName) {
            DB::unprepared("ALTER TABLE `{$tableName}` CHARACTER SET {$charset} COLLATE {$collation};");
        }

        // Get the list of all columns that have a collation
        $columns = DB::table('information_schema.columns')
            ->where('table_schema', $databaseName)
            ->whereNotNull('CHARACTER_SET_NAME')
            ->whereNotNull('COLLATION_NAME')
            ->where(function ($query) use ($charset, $collation) {
                $query->where('CHARACTER_SET_NAME', '!=', $charset)
                      ->orWhere('COLLATION_NAME', '!=', $collation);
            })
            ->get();

        // Iterate through the list and alter each column
        foreach ($columns as $column) {
            $null = $column->IS_NULLABLE == 'YES' ? 'NULL' : 'NOT NULL';

            $default = null;
            if (is_null($column->COLUMN_DEFAULT) || $column->COLUMN_DEFAULT == 'NULL') {
                //
            } else {
                $default = "DEFAULT '" . trim($column->COLUMN_DEFAULT, '\'') . "'";
            }

            $sql = "ALTER TABLE `{$column->TABLE_NAME}`
                    MODIFY `{$column->COLUMN_NAME}`
                    {$column->COLUMN_TYPE}
                    CHARACTER SET {$charset}
                    COLLATE {$collation}
                    {$null} {$default};";
            DB::unprepared($sql);
        }
    }
};
