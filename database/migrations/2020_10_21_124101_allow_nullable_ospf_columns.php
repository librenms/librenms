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
        Schema::table('ospf_areas', function (Blueprint $table) {
            $table->string('ospfAuthType', 64)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ospf_areas', function (Blueprint $table) {
            $table->string('ospfAuthType', 64)->nullable(false)->change();
        });
    }
};
