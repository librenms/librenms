<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('api_tokens') && Schema::hasTable('personal_access_tokens')) {
            $tokens = DB::table('api_tokens')->whereNotNull('token_hash')->get();

            foreach ($tokens as $token) {
                DB::table('personal_access_tokens')->insert([
                    'tokenable_type' => User::class,
                    'tokenable_id' => $token->user_id,
                    'name' => $token->description !== '' ? $token->description : 'api-token',
                    'token' => hash('sha256', (string) $token->token_hash),
                    'abilities' => json_encode(['*']),
                    'last_used_at' => null,
                    'expires_at' => $token->disabled ? now()->subDay() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::drop('api_tokens');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('api_tokens')) {
            Schema::create('api_tokens', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->string('token_hash')->nullable()->unique();
                $table->string('description', 100);
                $table->boolean('disabled')->default(0);
            });
        }
    }
};
