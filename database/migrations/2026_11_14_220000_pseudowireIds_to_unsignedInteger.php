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
        Schema::table('pseudowires', function (Blueprint $table): void {
            $table->unsignedInteger('cpwOid')->change();
            $table->unsignedInteger('peer_ldp_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Refuse to narrow back to signed INT if any value would not fit.
        $max = 2147483647;
        $outOfRange = DB::table('pseudowires')
            ->where('cpwOid', '>', $max)
            ->orWhere('peer_ldp_id', '>', $max)
            ->count();

        if ($outOfRange > 0) {
            throw new RuntimeException("Cannot roll back: $outOfRange pseudowires row(s) have cpwOid or peer_ldp_id above $max, which does not fit in a signed INT.");
        }

        Schema::table('pseudowires', function (Blueprint $table): void {
            $table->integer('cpwOid')->change();
            $table->integer('peer_ldp_id')->change();
        });
    }
};
