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
        Schema::table('slas', function (Blueprint $table) {
            $table->unsignedInteger('sla_nr')->change();
            $table->unsignedFloat('rtt')->nullable()->after('rtt_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('slas', function (Blueprint $table) {
            $table->integer('sla_nr')->change();
            $table->dropColumn('rtt');
        });
    }
};
