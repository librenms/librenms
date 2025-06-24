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
        Schema::table('ports_vlans', function (Blueprint $table) {
            $table->unsignedInteger('port_id')->default(0)->change();
            $table->unsignedInteger('vlan')->default(0)->change();
            $table->unsignedInteger('baseport')->default(0)->change();
            $table->unsignedBigInteger('priority')->default(0)->change();
            $table->string('state', 16)->default('unknown')->change();
            $table->unsignedInteger('cost')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ports_vlans', function (Blueprint $table) {
            $table->unsignedInteger('port_id')->change();
            $table->unsignedInteger('vlan')->change();
            $table->unsignedInteger('baseport')->change();
            $table->unsignedBigInteger('priority')->change();
            $table->string('state', 16)->change();
            $table->unsignedInteger('cost')->change();
        });
    }
};
