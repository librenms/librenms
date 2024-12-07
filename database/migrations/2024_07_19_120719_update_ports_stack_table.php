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
        if (! Schema::hasColumn('ports_stack', 'id')) {
            Schema::table('ports_stack', function (Blueprint $table) {
                $table->id()->first();
                $table->unsignedBigInteger('high_port_id')->nullable()->after('port_id_high');
                $table->unsignedBigInteger('low_port_id')->nullable()->after('port_id_low');
                $table->renameColumn('port_id_high', 'high_ifIndex');
                $table->renameColumn('port_id_low', 'low_ifIndex');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ports_stack', function (Blueprint $table) {
            $table->renameColumn('high_ifIndex', 'port_id_high');
            $table->renameColumn('low_ifIndex', 'port_id_low');
            $table->dropColumn(['id', 'high_port_id', 'low_port_id']);
        });
    }
};
