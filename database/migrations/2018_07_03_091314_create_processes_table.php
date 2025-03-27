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
        Schema::create('processes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('device_id')->index();
            $table->integer('pid');
            $table->integer('vsz');
            $table->integer('rss');
            $table->string('cputime', 12);
            $table->string('user', 50);
            $table->text('command');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('processes');
    }
};
