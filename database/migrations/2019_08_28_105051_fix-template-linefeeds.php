<?php

use Illuminate\Database\Migrations\Migration;

class FixTemplateLinefeeds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('alert_templates')->update(['template' => DB::raw('REPLACE(`template`, \'\\\\r\\\\n\', char(10))')]);
        DB::table('alert_templates')->update(['template' => DB::raw('REPLACE(`template`, \'\\\\n\', \'\')')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
