<?php

use Illuminate\Database\Migrations\Migration;

class FixInvalidDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (\LibreNMS\DB\Eloquent::getDriver() != 'mysql') {
            return;
        }

        // Too prevent errors like, Incorrect datetime value: '0000-00-00 00:00:00'
        \LibreNMS\DB\Eloquent::setStrictMode(false);

        $database_name = DB::connection()->getDatabaseName();

        $columns = DB::table('information_schema.columns')
                     ->where('table_schema', $database_name)
                     ->whereIn('COLUMN_TYPE', ['datetime', 'timestamp'])
                     ->get(['TABLE_NAME', 'COLUMN_NAME']);

        foreach ($columns as $column) {
            DB::table($column->TABLE_NAME)
              ->where($column->COLUMN_NAME, '0000-00-00 00:00:00')
              ->update([$column->COLUMN_NAME => '1970-01-02 00:00:01']);
        }

        \LibreNMS\DB\Eloquent::setStrictMode(true);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
