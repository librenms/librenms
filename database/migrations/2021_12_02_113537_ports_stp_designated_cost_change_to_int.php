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
        Schema::table('ports_stp', function (Blueprint $table) {
            $table->unsignedInteger('designatedCost')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ports_stp', function (Blueprint $table) {
            $table->smallInteger('designatedCost')->unsigned()->change();
        });
    }
};
