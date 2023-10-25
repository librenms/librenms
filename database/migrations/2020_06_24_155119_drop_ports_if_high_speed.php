<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('ports', function (Blueprint $table) {
            $table->dropColumn(['ifHighSpeed', 'ifHighSpeed_prev']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->integer('ifHighSpeed')->nullable()->after('ifPromiscuousMode');
            $table->integer('ifHighSpeed_prev')->nullable()->after('ifHighSpeed');
        });
    }
};
