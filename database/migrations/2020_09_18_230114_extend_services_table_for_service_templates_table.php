<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ExtendServicesTableForServiceTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedInteger('service_template_id')->default(0);
            $table->string('service_name')->nullable()->default(null);
            $table->text('service_desc')->nullable()->default(null)->change();
            $table->text('service_param')->nullable()->default(null)->change();
            $table->boolean('service_ignore')->default(0)->change();
            $table->text('service_ip')->nullable()->default(null)->change();
            $table->text('service_ds')->nullable()->default(null)->change();
            $table->text('service_message')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'service_template_id',
                'service_name',
            ]);
            $table->text('service_desc')->change();
            $table->text('service_param')->change();
            $table->boolean('service_ignore')->change();
            $table->text('service_ip')->change();
            $table->text('service_ds')->change();
            $table->text('service_message')->change();
        });
    }
}
