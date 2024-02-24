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
        Schema::create('dashboards', function (Blueprint $table) {
            $table->increments('dashboard_id');
            $table->unsignedInteger('user_id')->default(0);
            $table->string('dashboard_name');
            $table->boolean('access')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('dashboards');
    }
};
