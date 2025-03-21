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
        Schema::dropIfExists('ospfv3_nbrs');
        Schema::create('ospfv3_nbrs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('ospfv3_instance_id');
            $table->unsignedInteger('port_id')->nullable();
            $table->string('router_id', 32);
            $table->unsignedInteger('ospfv3NbrIfIndex');
            $table->unsignedInteger('ospfv3NbrIfInstId');
            $table->unsignedInteger('ospfv3NbrRtrId');
            $table->string('ospfv3NbrAddressType', 32);
            $table->string('ospfv3NbrAddress', 39);
            $table->integer('ospfv3NbrOptions');
            $table->integer('ospfv3NbrPriority');
            $table->string('ospfv3NbrState', 32);
            $table->unsignedInteger('ospfv3NbrEvents');
            $table->unsignedInteger('ospfv3NbrLsRetransQLen');
            $table->string('ospfv3NbrHelloSuppressed', 32);
            $table->unsignedInteger('ospfv3NbrIfId');
            $table->string('ospfv3NbrRestartHelperStatus', 32);
            $table->unsignedInteger('ospfv3NbrRestartHelperAge');
            $table->string('ospfv3NbrRestartHelperExitReason', 32);
            $table->string('context_name', 128)->nullable();
            $table->unique(['device_id', 'ospfv3NbrIfIndex', 'ospfv3NbrIfInstId', 'ospfv3NbrRtrId', 'context_name'], 'ospfv3_nbrs_device_id_index_context_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ospfv3_nbrs');
        Schema::create('ospfv3_nbrs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('port_id')->nullable();
            $table->unsignedInteger('ospfv3_instance_id');
            $table->string('ospfv3_nbr_id', 32);
            $table->integer('ospfv3NbrIfId');
            $table->integer('ospfv3NbrIfIndex');
            $table->integer('ospfv3NbrIfInstId');
            $table->string('ospfv3NbrAddressType', 32);
            $table->string('ospfv3NbrAddress', 39);
            $table->string('ospfv3NbrRtrId', 32);
            $table->integer('ospfv3NbrOptions');
            $table->integer('ospfv3NbrPriority');
            $table->string('ospfv3NbrState', 32);
            $table->integer('ospfv3NbrEvents')->nullable();
            $table->integer('ospfv3NbrLsRetransQLen');
            $table->string('ospfv3NbrHelloSuppressed', 32);
            $table->string('context_name', 128)->nullable();
            $table->unique(['device_id', 'ospfv3_instance_id', 'ospfv3_nbr_id', 'context_name'], 'ospfv3_nbrs_device_id_ospfv3_nbr_id_context_name_unique');
        });
    }
};
