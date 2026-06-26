<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cef_switching', function (Blueprint $table) {
            $table->bigInteger('punt2host')->change();
            $table->bigInteger('punt2host_prev')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cef_switching', function (Blueprint $table) {
            $table->integer('punt2host')->change();
            $table->integer('punt2host_prev')->change();
        });
    }
};
