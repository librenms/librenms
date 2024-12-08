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
        Schema::table('ports', function (Blueprint $table) {
            $table->string('ifVlan', 8)->nullable()->default(null)->change();
            $table->dropColumn(['ifHardType', 'counter_in', 'counter_out', 'detailed', 'ifPromiscuousMode']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->string('ifVlan', 8)->default('')->change();
            $table->string('ifHardType', 64)->nullable()->after('ifPhysAddress');
            $table->integer('counter_in')->nullable()->after('ifVrf');
            $table->integer('counter_out')->nullable()->after('counter_in');
            $table->boolean('detailed')->default(0)->after('disabled');
            $table->string('ifPromiscuousMode', 12)->nullable()->after('ifConnectorPresent');
        });
    }
};
