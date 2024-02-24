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
        Schema::create('notifications_attribs', function (Blueprint $table) {
            $table->increments('attrib_id');
            $table->unsignedInteger('notifications_id');
            $table->unsignedInteger('user_id');
            $table->string('key')->default('');
            $table->string('value')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('notifications_attribs');
    }
};
