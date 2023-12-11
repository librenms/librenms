<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ports_nac', function (Blueprint $table) {
            $table->timestamps();
            $table->boolean('historical')->default(0);
        });
        DB::table('ports_nac')->update(['created_at' => \Carbon\Carbon::now()]);
        DB::table('ports_nac')->update(['updated_at' => \Carbon\Carbon::now()]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ports_nac', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at', 'historical']);
        });
    }
};
