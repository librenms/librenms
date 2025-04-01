<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::drop('bouncer_permissions');
        Schema::drop('assigned_roles');
        Schema::drop('bouncer_roles');
        Schema::drop('abilities');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
