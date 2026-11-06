<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('callback')->where('name', 'error_reporting_uuid')->delete();
    }

    public function down(): void
    {
        // deleted data cannot be restored
    }
};
