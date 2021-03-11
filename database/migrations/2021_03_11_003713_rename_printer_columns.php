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
            $table->renameColumn('toner_id', 'id');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('toner_index', 'printer_index');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('toner_type', 'printer_type');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('toner_oid', 'printer_oid');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('toner_descr', 'printer_descr');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('toner_capacity', 'printer_capacity');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('toner_current', 'printer_current');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('toner_capacity_oid', 'printer_capacity_oid');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->string('printer_descr', 255)->change();
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
            $table->renameColumn('id', 'toner_id');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('printer_index', 'toner_index');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('printer_type', 'toner_type');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('printer_oid', 'toner_oid');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('printer_descr', 'toner_descr');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('printer_capacity', 'toner_capacity');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('printer_current', 'toner_current');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->renameColumn('printer_capacity_oid', 'toner_capacity_oid');
        });

        Schema::table('printer_supplies', function (Blueprint $table) {
            $table->string('toner_descr', 32)->change();
        });
    }
}
