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
        Schema::create('service_templates', function (Blueprint $table) {
            $table->renameColumn('type', 'check');
            $table->renameColumn('dtype', 'type');
            $table->renameColumn('drules', 'rules');
            $table->dropColumn(['dgtype', 'dgrules']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('service_templates', function (Blueprint $table) {
            $table->renameColumn('check', 'type');
            $table->renameColumn('type', 'dtype');
            $table->renameColumn('rules', 'drules');
            $table->string('dgtype', 16)->default('static');
            $table->text('dgrules')->nullable();
        });
    }
}
