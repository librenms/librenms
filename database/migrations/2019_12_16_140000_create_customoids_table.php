<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomoidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customoids', function (Blueprint $table) {
            $table->increments('customoid_id');
            $table->unsignedInteger('device_id')->default(0);
            $table->string('customoid_descr', 255)->nullable()->default('');
            $table->tinyInteger('customoid_deleted')->default(0);
            $table->double('customoid_current')->nullable();
            $table->double('customoid_prev')->nullable();
            $table->string('customoid_oid', 255)->nullable();
            $table->string('customoid_datatype', 20)->default('GAUGE');
            $table->string('customoid_unit', 20)->nullable();
            $table->unsignedInteger('customoid_divisor')->default(1);
            $table->unsignedInteger('customoid_multiplier')->default(1);
            $table->double('customoid_limit')->nullable();
            $table->double('customoid_limit_warn')->nullable();
            $table->double('customoid_limit_low')->nullable();
            $table->double('customoid_limit_low_warn')->nullable();
            $table->tinyInteger('customoid_alert')->default(0);
            $table->tinyInteger('customoid_passed')->default(0);
            if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
                $table->timestamp('lastupdate')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            } else {
                $table->timestamp('lastupdate')->useCurrent();
            }
            $table->string('user_func', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customoids');
    }
}
