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
        Schema::table('ospfv3_ports', function (Blueprint $table) {
            $table->string('ospfv3IfType', 32)->default('')->change();
            $table->unsignedInteger('ospfv3IfPollInterval')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ospfv3_ports', function (Blueprint $table) {
            $table->string('ospfv3IfType', 32)->change();
            $table->unsignedInteger('ospfv3IfPollInterval')->change();
        });
    }
};
