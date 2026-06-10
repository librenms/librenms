<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('alert_templates')->update(['template' => DB::raw('REPLACE(`template`, \'\\\\r\\\\n\', char(10))')]);
        DB::table('alert_templates')->update(['template' => DB::raw('REPLACE(`template`, \'\\\\n\', \'\')')]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
