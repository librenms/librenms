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
        Schema::table('pseudowires', function (Blueprint $table): void {
            $table->integer('cpwOid')->change();
            $table->integer('peer_ldp_id')->change();
        });
    }
};
