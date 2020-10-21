<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateServicesTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_group_id')->index();
            $table->text('ip')->nullable()->default(null);
            $table->string('type');
            $table->text('desc')->nullable()->default(null);
            $table->text('param')->nullable()->default(null);
            $table->boolean('ignore')->default(0);
            if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
                $table->timestamp('changed')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
            } else {
                $table->timestamp('changed')->useCurrent();
            }
            $table->boolean('disabled')->default(0);
            $table->string('name');
        });
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedInteger('service_template_id')->default(0);
            $table->string('service_name')->nullable()->default(null);
            $table->unsignedInteger('service_template_changed')->default(0);
        });
        Schema::create('service_templates_perms', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('service_template_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('service_templates');
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'service_template_id',
                'service_name',
                'service_template_changed',
            ]);
        });
        Schema::drop('service_templates_perms');
    }
}
