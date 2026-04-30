<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alert_rules', function (Blueprint $table) {
            $table->string('extra')->default('{}')->change();
            $table->text('builder')->default('{}')->change();
            $table->text('query')->default('')->change();
        });

        Schema::table('bills', function (Blueprint $table) {
            foreach ([
                'rate_95th_in', 'rate_95th_out', 'rate_95th',
                'total_data', 'total_data_in', 'total_data_out',
                'rate_average_in', 'rate_average_out', 'rate_average',
            ] as $col) {
                $table->bigInteger($col)->default(0)->change();
            }
            $table->string('dir_95th', 3)->default('in')->change();
            $table->dateTime('bill_last_calc')->default('1970-01-01 00:00:00')->change();
            $table->string('bill_custid', 64)->default('')->change();
            $table->string('bill_ref', 64)->default('')->change();
            $table->string('bill_notes', 256)->default('')->change();
            $table->boolean('bill_autoadded')->default(0)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('realname', 64)->default('')->change();
            $table->string('email', 64)->default('')->change();
            $table->char('descr', 30)->default('')->change();
        });
    }

    public function down(): void
    {
        Schema::table('alert_rules', function (Blueprint $table) {
            $table->string('extra')->change();
            $table->text('builder')->change();
            $table->text('query')->change();
        });

        Schema::table('bills', function (Blueprint $table) {
            foreach ([
                'rate_95th_in', 'rate_95th_out', 'rate_95th',
                'total_data', 'total_data_in', 'total_data_out',
                'rate_average_in', 'rate_average_out', 'rate_average',
            ] as $col) {
                $table->bigInteger($col)->change();
            }
            $table->string('dir_95th', 3)->change();
            $table->dateTime('bill_last_calc')->change();
            $table->string('bill_custid', 64)->change();
            $table->string('bill_ref', 64)->change();
            $table->string('bill_notes', 256)->change();
            $table->boolean('bill_autoadded')->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('realname', 64)->change();
            $table->string('email', 64)->change();
            $table->char('descr', 30)->change();
        });
    }
};
