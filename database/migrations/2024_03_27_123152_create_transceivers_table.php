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
        Schema::create('transceivers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('device_id');
            $table->bigInteger('port_id');
            $table->string('index');
            $table->integer('entity_physical_index')->nullable();
            $table->string('type', 128)->nullable();
            $table->string('vendor', 16)->nullable();
            $table->string('oui', 16)->nullable();
            $table->string('model', 32)->nullable();
            $table->string('revision', 16)->nullable();
            $table->string('serial', 32)->nullable();
            $table->date('date')->nullable();
            $table->boolean('ddm')->nullable();
            $table->string('encoding', 16)->nullable();
            $table->string('cable', 16)->nullable();
            $table->integer('distance')->nullable();
            $table->integer('wavelength')->nullable();
            $table->string('connector', 16)->nullable();
            $table->smallInteger('channels')->default(1);
            $table->index(['device_id', 'entity_physical_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transceivers');
    }
};
