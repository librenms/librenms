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
        Schema::table('availability', function (Blueprint $table) {
            $table->decimal('availability_perc', 9, 6)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('availability', function (Blueprint $table) {
            $table->float('availability_perc', 6, 6)->default(0)->change();
        });
    }
};
