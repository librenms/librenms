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
        Schema::dropIfExists('callback');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('callback', function (Blueprint $table) {
            $table->increments('callback_id');
            $table->string('name', 64);
            $table->string('value', 64);
        });
    }
};
