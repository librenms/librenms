<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable('dbSchema') && DB::table('dbSchema')->exists()) {
            if (DB::table('dbSchema')->value('version') != 1000) {
                $error = 'Unsupported upgrade path! You need to update to version 23.7.0 first!';
                Log::error($error);
                exit($error);
            }
        }

        Schema::dropIfExists('dbSchema');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::create('dbSchema', function (Blueprint $table) {
            $table->integer('version')->default(0)->primary();
        });

        if (! DB::table('dbSchema')->exists()) {
            DB::table('dbSchema')->insert(['version' => 1000]);
        }
    }
};
