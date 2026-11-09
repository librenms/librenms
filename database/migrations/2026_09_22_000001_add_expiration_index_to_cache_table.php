<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('cache', 'cache_expiration_index')) {
            Schema::table('cache', function (Blueprint $table): void {
                $table->index('expiration');
            });
        }

        if (! Schema::hasIndex('cache_locks', 'cache_locks_expiration_index')) {
            $connection = Schema::getConnection();
            if (in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
                $table = $connection->getQueryGrammar()->wrapTable('cache_locks');
                $connection->statement("ALTER TABLE $table ROW_FORMAT=DYNAMIC");
            }

            Schema::table('cache_locks', function (Blueprint $table): void {
                $table->index('expiration');
            });
        }
    }

    public function down(): void
    {
        Schema::table('cache', function (Blueprint $table): void {
            $table->dropIndex(['expiration']);
        });

        Schema::table('cache_locks', function (Blueprint $table): void {
            $table->dropIndex(['expiration']);
        });
    }
};
