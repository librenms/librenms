<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('ports_vdsl', function (Blueprint $table) {
            $table->unsignedInteger('port_id')->unique();
            $table->timestamp('port_vdsl_updated')->useCurrent();
            $table->integer('xdsl2LineStatusAttainableRateDs')->default(0);
            $table->integer('xdsl2LineStatusAttainableRateUs')->default(0);
            $table->integer('xdsl2ChStatusActDataRateXtur')->default(0);
            $table->integer('xdsl2ChStatusActDataRateXtuc')->default(0);
            $table->decimal('xdsl2LineStatusActAtpDs')->default(0);
            $table->decimal('xdsl2LineStatusActAtpUs')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('ports_vdsl');
    }
};
