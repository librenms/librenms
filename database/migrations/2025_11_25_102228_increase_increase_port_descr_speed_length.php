<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('ports', function (Blueprint $table) {
            $table->string('port_descr_speed')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('ports', function (Blueprint $table) {
            $table->string('port_descr_speed', 32)->nullable();
        });
    }
};
