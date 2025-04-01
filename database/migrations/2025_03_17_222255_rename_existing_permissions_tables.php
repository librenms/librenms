<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->rename('bouncer_permissions');
        });
        Schema::table('roles', function (Blueprint $table) {
            $table->rename('bouncer_roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bouncer_permissions', function (Blueprint $table) {
            $table->rename('permissions');
        });
        Schema::table('bouncer_roles', function (Blueprint $table) {
            $table->rename('roles');
        });
    }
};
