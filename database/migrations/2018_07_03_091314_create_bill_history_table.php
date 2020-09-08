<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBillHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_history', function (Blueprint $table) {
            $table->increments('bill_hist_id');
            $table->unsignedInteger('bill_id')->index();
            $table->timestamp('updated')->useCurrent();
            $table->dateTime('bill_datefrom');
            $table->dateTime('bill_dateto');
            $table->text('bill_type');
            $table->bigInteger('bill_allowed');
            $table->bigInteger('bill_used');
            $table->bigInteger('bill_overuse');
            $table->decimal('bill_percent', 10);
            $table->bigInteger('rate_95th_in');
            $table->bigInteger('rate_95th_out');
            $table->bigInteger('rate_95th');
            $table->string('dir_95th', 3);
            $table->bigInteger('rate_average');
            $table->bigInteger('rate_average_in');
            $table->bigInteger('rate_average_out');
            $table->bigInteger('traf_in');
            $table->bigInteger('traf_out');
            $table->bigInteger('traf_total');
            $table->binary('pdf')->nullable();
            $table->unique(['bill_id', 'bill_datefrom', 'bill_dateto']);
        });

        if (\LibreNMS\DB\Eloquent::getDriver() == 'mysql') {
            \DB::statement('ALTER TABLE `bill_history` CHANGE `pdf` `pdf` longblob NULL ;');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bill_history');
    }
}
