<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sensors')
            ->where('group', 'transceiver')
            ->update(['group' => null, 'category' => 'transceiver']);
    }

    public function down(): void
    {
        DB::table('sensors')
            ->where('category', 'transceiver')
            ->update(['group' => 'transceiver', 'category' => null]);
    }
};
