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
            $table->string('cpsIfStickyEnable', 1)->nullable()->after('ifVrf');
            $table->integer('cpsIfMaxSecureMacAddr')->nullable()->after('ifVrf');
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
            $table->dropColumn(['cpsIfMaxSecureMacAddr', 'cpsIfStickyEnable']);
        });
    }
};
