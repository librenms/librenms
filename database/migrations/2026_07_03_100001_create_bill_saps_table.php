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
        Schema::create('bill_saps', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('bill_id');
            $table->unsignedInteger('sap_id');
            $table->boolean('bill_sap_autoadded')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('bill_saps');
    }
};
