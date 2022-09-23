<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('device_id');
            $table->string('hostname', 128)->index();
            $table->string('sysName', 128)->nullable()->index();
            $table->binary('ip')->nullable();
            $table->string('community')->nullable();
            $table->enum('authlevel', ['noAuthNoPriv', 'authNoPriv', 'authPriv'])->nullable();
            $table->string('authname', 64)->nullable();
            $table->string('authpass', 64)->nullable();
            $table->enum('authalgo', ['MD5', 'SHA'])->nullable();
            $table->string('cryptopass', 64)->nullable();
            $table->enum('cryptoalgo', ['AES', 'DES', ''])->nullable();
            $table->string('snmpver', 4)->default('v2c');
            $table->smallInteger('port')->unsigned()->default(161);
            $table->string('transport', 16)->default('udp');
            $table->integer('timeout')->nullable();
            $table->integer('retries')->nullable();
            $table->boolean('snmp_disable')->default(0);
            $table->unsignedInteger('bgpLocalAs')->nullable();
            $table->string('sysObjectID', 128)->nullable();
            $table->text('sysDescr')->nullable();
            $table->text('sysContact')->nullable();
            $table->text('version')->nullable();
            $table->text('hardware')->nullable();
            $table->text('features')->nullable();
            $table->unsignedInteger('location_id')->nullable();
            $table->string('os', 32)->nullable()->index();
            $table->boolean('status')->default(0)->index();
            $table->string('status_reason', 50);
            $table->boolean('ignore')->default(0);
            $table->boolean('disabled')->default(0);
            $table->bigInteger('uptime')->nullable();
            $table->unsignedInteger('agent_uptime')->default(0);
            $table->timestamp('last_polled')->nullable()->index();
            $table->timestamp('last_poll_attempted')->nullable()->index();
            $table->float('last_polled_timetaken', 5)->nullable();
            $table->float('last_discovered_timetaken', 5)->nullable();
            $table->timestamp('last_discovered')->nullable();
            $table->timestamp('last_ping')->nullable();
            $table->float('last_ping_timetaken')->nullable();
            $table->text('purpose')->nullable();
            $table->string('type', 20)->default('');
            $table->text('serial')->nullable();
            $table->string('icon')->nullable();
            $table->integer('poller_group')->default(0);
            $table->boolean('override_sysLocation')->nullable()->default(0);
            $table->text('notes')->nullable();
            $table->integer('port_association_mode')->default(1);
            $table->integer('max_depth')->default(0);
        });

        if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
            \DB::statement('ALTER TABLE `devices` CHANGE `ip` `ip` varbinary(16) NULL ;');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('devices');
    }
}
