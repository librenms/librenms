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
        Schema::create('port_security', function (Blueprint $table) {
            $table->integer('port_id')->unsigned()->default(0);
            $table->integer('device_id')->unsigned()->default(0)->after('port_id');
            $table->integer('cpsIfMaxSecureMacAddr')->nullable()->after('device_id');
            $table->string('cpsIfStickyEnable')->nullable()->after('cpsIfMaxSecureMacAddr');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('port_security');
    }
};
