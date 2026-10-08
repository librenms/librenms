<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('config')->where('config_name', 'reporting.error')->delete();
        DB::table('callback')->where('name', 'error_reporting_uuid')->delete();
    }
};
