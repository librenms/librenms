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
        Schema::table('locations', function (Blueprint $table) {
            $table->double('lat')->nullable()->change();
            $table->double('lng')->nullable()->change();
        });

        Schema::table('slas', function (Blueprint $table) {
            $table->double('rtt')->unsigned()->nullable()->change();
        });
    }
};
