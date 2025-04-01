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
        Schema::table('applications', function (Blueprint $table) {
            $table->string('app_instance')->default('')->change();
            $table->string('app_status', 1024)->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('app_instance')->default(null)->change();
            $table->string('app_status', 1024)->default(null)->change();
        });
    }
};
