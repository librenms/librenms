<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStorageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storage', function (Blueprint $table) {
            $table->increments('storage_id');
            $table->unsignedInteger('device_id')->index();
            $table->string('storage_mib', 16);
            $table->string('storage_index', 64)->nullable();
            $table->string('storage_type', 32)->nullable();
            $table->text('storage_descr');
            $table->bigInteger('storage_size');
            $table->integer('storage_units');
            $table->bigInteger('storage_used')->default(0);
            $table->bigInteger('storage_free')->default(0);
            $table->integer('storage_perc')->default(0);
            $table->integer('storage_perc_warn')->nullable()->default(60);
            $table->boolean('storage_deleted')->default(0);
            $table->unique(['device_id', 'storage_mib', 'storage_index']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('storage');
    }
}
