<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePrinterColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('toner_id', 'supply_id');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('toner_index', 'supply_index');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('toner_type', 'supply_type');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('toner_oid', 'supply_oid');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('toner_descr', 'supply_descr');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('toner_capacity', 'supply_capacity');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('toner_current', 'supply_current');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('toner_capacity_oid', 'supply_capacity_oid');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->string('supply_descr', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('supply_id', 'toner_id');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('supply_index', 'toner_index');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('supply_type', 'toner_type');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('supply_oid', 'toner_oid');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('supply_descr', 'toner_descr');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('supply_capacity', 'toner_capacity');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('supply_current', 'toner_current');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('supply_capacity_oid', 'toner_capacity_oid');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->string('toner_descr', 32)->change();
        });
    }
}
