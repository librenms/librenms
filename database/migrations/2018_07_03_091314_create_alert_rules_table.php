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
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->text('rule');
            $table->enum('severity', ['ok', 'warning', 'critical']);
            $table->string('extra');
            $table->boolean('disabled');
            $table->string('name')->unique();
            $table->text('query');
            $table->text('builder');
            $table->string('proc', 80)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('alert_rules');
    }
};
