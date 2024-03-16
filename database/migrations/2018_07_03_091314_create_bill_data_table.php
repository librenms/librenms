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
        Schema::create('bill_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('bill_id')->index();
            $table->dateTime('timestamp');
            $table->integer('period');
            $table->bigInteger('delta');
            $table->bigInteger('in_delta');
            $table->bigInteger('out_delta');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('bill_data');
    }
};
