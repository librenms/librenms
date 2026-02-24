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
            $table->bigInteger('drop')->change();
            $table->bigInteger('punt')->change();
            $table->bigInteger('drop_prev')->change();
            $table->bigInteger('punt_prev')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cef_switching', function (Blueprint $table) {
            $table->integer('drop')->change();
            $table->integer('punt')->change();
            $table->integer('drop_prev')->change();
            $table->integer('punt_prev')->change();
        });
    }
};
