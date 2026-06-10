<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('juniAtmVp', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('juniAtmVp_id');
            $table->unsignedInteger('port_id')->index();
            $table->unsignedInteger('vp_id');
            $table->string('vp_descr', 32);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('juniAtmVp');
    }
};
