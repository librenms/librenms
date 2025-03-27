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
        Schema::create('devices_attribs', function (Blueprint $table) {
            $table->increments('attrib_id');
            $table->unsignedInteger('device_id')->index();
            $table->string('attrib_type', 32);
            $table->text('attrib_value');
            if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
                $table->timestamp('updated')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            } else {
                $table->timestamp('updated')->useCurrent();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('devices_attribs');
    }
};
