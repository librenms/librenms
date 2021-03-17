<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ServiceTemplatesCleanup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_templates', function (Blueprint $table) {
            $table->renameColumn('type', 'check');
            $table->renameColumn('dtype', 'type');
            $table->renameColumn('drules', 'rules');
            if (\LibreNMS\DB\Eloquent::getDriver() !== 'sqlite') {
                $table->dropColumn(['dgtype', 'dgrules']);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_templates', function (Blueprint $table) {
            $table->renameColumn('type', 'dtype');
            $table->renameColumn('check', 'type');
            $table->renameColumn('rules', 'drules');

            if (\LibreNMS\DB\Eloquent::getDriver() !== 'sqlite') {
                $table->string('dgtype', 16)->default('static');
                $table->text('dgrules')->nullable();
            }
        });
    }
}
