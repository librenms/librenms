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
        Schema::create('alert_group_map', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('rule_id');
            $table->unsignedInteger('group_id');
            $table->unique(['rule_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('alert_group_map');
    }
};
