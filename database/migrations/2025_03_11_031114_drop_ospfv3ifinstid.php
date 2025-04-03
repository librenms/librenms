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
        Schema::table('ospfv3_ports', function (Blueprint $table) {
            $table->dropColumn('ospfv3IfInstId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ospfv3_ports', function (Blueprint $table) {
            $table->integer('ospfv3IfInstId')->after('ospfv3IfIndex');
        });
    }
};
