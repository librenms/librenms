<?php

use App\Models\PortsFdb;
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
        Schema::table('ports_fdb', function (Blueprint $table) {
            $table->timestamps();
        });

        // Let's get a value for existing PortsFdb data :
        DB::table('ports_fdb')->update(['created_at' => \Carbon\Carbon::now()]);
        DB::table('ports_fdb')->update(['updated_at' => \Carbon\Carbon::now()]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ports_fdb', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};
