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
        Schema::create('ospfv3_instances', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('ospfv3_instance_id');
            $table->string('ospfv3RouterId', 32);
            $table->string('ospfv3AdminStatus', 32);
            $table->string('ospfv3VersionNumber', 32);
            $table->string('ospfv3AreaBdrRtrStatus', 32);
            $table->string('ospfv3ASBdrRtrStatus', 32);
            $table->integer('ospfv3ExtLsaCount');
            $table->integer('ospfv3OriginateNewLsas');
            $table->integer('ospfv3RxNewLsas');
            $table->integer('ospfv3ExtAreaLsdbLimit')->nullable();
            $table->integer('ospfv3AsScopeLsaCount')->nullable();
            $table->integer('ospfv3ExitOverflowInterval')->nullable();
            $table->integer('ospfv3AsScopeLsaCksumSum')->nullable();
            $table->string('context_name', 128)->nullable();
            $table->unique(['device_id', 'ospfv3_instance_id', 'context_name'], 'ospfv3_instances_device_id_ospfv3_instance_id_context_name_uniq');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('ospfv3_instances');
    }
};
