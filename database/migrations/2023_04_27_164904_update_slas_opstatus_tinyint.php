<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('slas', function (Blueprint $table) {
            $table->renameColumn('opstatus', 'opstatus_old');
        });

        Schema::table('slas', function (Blueprint $table) {
            $table->unsignedTinyInteger('opstatus')->default(0)->after('opstatus_old');
        });

        /** @phpstan-ignore-next-line */
        foreach (\App\Models\Sla::where('opstatus_old', '>', 0)->select(['sla_id', 'opstatus_old'])->lazy() as $sla) {
            /** @phpstan-ignore-next-line */
            $sla->opstatus = $sla->opstatus_old;
            $sla->save();
        }

        Schema::table('slas', function (Blueprint $table) {
            $table->dropColumn('opstatus_old');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slas', function (Blueprint $table) {
            $table->boolean('opstatus')->default(0)->change();
        });
    }
};
