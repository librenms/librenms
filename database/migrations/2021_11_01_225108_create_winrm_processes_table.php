<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWinrmProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('winrm_processes', function (Blueprint $table) {
            $table->unsignedInteger('device_id');
            $table->integer('pid');
            $table->string('name', 250);
            $table->string('process_name', 250)->nullable();
            $table->string('username', 250)->nullable();
            $table->biginteger('npm')->nullable();
            $table->biginteger('pm')->nullable();
            $table->biginteger('ws')->nullable();
            $table->biginteger('vm')->nullable();
            $table->decimal('cpu', $precision = 18, $scale = 6)->nullable();
            $table->unique(['device_id', 'pid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('winrm_processes');
    }
}
