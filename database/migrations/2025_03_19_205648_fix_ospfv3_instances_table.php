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
        Schema::dropIfExists('ospfv3_instances');
        Schema::create('ospfv3_instances', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id');
            $table->string('router_id', 32);
            $table->unsignedInteger('ospfv3RouterId');
            $table->string('ospfv3AdminStatus', 32);
            $table->string('ospfv3VersionNumber', 32);
            $table->string('ospfv3AreaBdrRtrStatus', 32);
            $table->string('ospfv3ASBdrRtrStatus', 32);
            $table->unsignedInteger('ospfv3OriginateNewLsas');
            $table->unsignedInteger('ospfv3RxNewLsas');
            $table->unsignedInteger('ospfv3ExtLsaCount');
            $table->integer('ospfv3ExtAreaLsdbLimit');
            $table->unsignedInteger('ospfv3AsScopeLsaCount');
            $table->unsignedInteger('ospfv3AsScopeLsaCksumSum');
            $table->unsignedInteger('ospfv3ExitOverflowInterval');
            $table->unsignedInteger('ospfv3ReferenceBandwidth');
            $table->string('ospfv3RestartSupport', 32);
            $table->unsignedInteger('ospfv3RestartInterval');
            $table->string('ospfv3RestartStrictLsaChecking', 32);
            $table->string('ospfv3RestartStatus', 32);
            $table->unsignedInteger('ospfv3RestartAge');
            $table->string('ospfv3RestartExitReason', 32);
            $table->string('ospfv3StubRouterSupport', 32);
            $table->string('ospfv3StubRouterAdvertisement', 32);
            $table->unsignedInteger('ospfv3DiscontinuityTime');
            $table->unsignedInteger('ospfv3RestartTime');
            $table->string('context_name', 128)->nullable();
            $table->unique(['device_id', 'context_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ospfv3_instances');
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
};
