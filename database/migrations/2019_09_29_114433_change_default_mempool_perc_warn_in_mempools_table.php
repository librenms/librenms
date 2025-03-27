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
        Schema::table('mempools', function (Blueprint $table) {
            $table->integer('mempool_perc_warn')->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('mempools', function (Blueprint $table) {
            $table->integer('mempool_perc_warn')->default('75')->change();
        });
    }
};
