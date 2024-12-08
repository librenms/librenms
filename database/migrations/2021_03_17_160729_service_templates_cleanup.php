<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('service_templates', function (Blueprint $table) {
            $table->renameColumn('type', 'check');
        });
        Schema::table('service_templates', function (Blueprint $table) {
            $table->renameColumn('dtype', 'type');
        });
        Schema::table('service_templates', function (Blueprint $table) {
            $table->renameColumn('drules', 'rules');
        });
        if (\LibreNMS\DB\Eloquent::getDriver() !== 'sqlite') {
            Schema::table('service_templates', function (Blueprint $table) {
                $table->dropColumn(['dgtype', 'dgrules']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('service_templates', function (Blueprint $table) {
            $table->renameColumn('type', 'dtype');
        });
        Schema::table('service_templates', function (Blueprint $table) {
            $table->renameColumn('check', 'type');
        });
        Schema::table('service_templates', function (Blueprint $table) {
            $table->renameColumn('rules', 'drules');
        });
        if (\LibreNMS\DB\Eloquent::getDriver() !== 'sqlite') {
            Schema::table('service_templates', function (Blueprint $table) {
                $table->string('dgtype', 16)->default('static');
                $table->text('dgrules')->nullable();
            });
        }
    }
};
