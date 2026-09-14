<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use LibreNMS\Tests\DBTestCase;

class ApiAccessControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testStoreGeneratesHashedTokenWithPrefixedSessionPlain(): void
    {
        $user = User::factory()->admin()->create(['enabled' => 1]);

        $response = $this->actingAs($user)->post(route('api-access.store'), [
            'description' => 'Test Token Description',
            'expires_in' => 30,
        ]);

        $response->assertRedirect(route('api-access.index'));
        $response->assertSessionHas('api_token_plain');

        $plainToken = session('api_token_plain');
        $this->assertStringContainsString('|', $plainToken);

        [$id, $secret] = explode('|', $plainToken, 2);

        $token = PersonalAccessToken::find($id);
        $this->assertNotNull($token);
        $this->assertSame($user->user_id, (int) $token->tokenable_id);
        $this->assertSame('Test Token Description', $token->name);
        $this->assertNotNull($token->expires_at);

        // Database stores the sha256 hash, not the plain secret or prefixed token
        $this->assertSame(hash('sha256', $secret), $token->token);
        $this->assertNotSame($secret, $token->token);
        $this->assertNotSame($plainToken, $token->token);
    }

    public function testResetGeneratesNewHashedTokenWithPrefixedSessionPlain(): void
    {
        $user = User::factory()->admin()->create(['enabled' => 1]);
        $token = $user->createToken('Initial Description');
        $pat = $token->accessToken;
        $oldHash = $pat->token;

        $response = $this->actingAs($user)->post(route('api-access.reset', $pat->id));

        $response->assertRedirect(route('api-access.index'));
        $response->assertSessionHas('api_token_plain');

        $newPlainToken = session('api_token_plain');
        $this->assertStringContainsString('|', $newPlainToken);

        [$id, $newSecret] = explode('|', $newPlainToken, 2);

        $newToken = PersonalAccessToken::find($id);
        $this->assertNotNull($newToken);
        $this->assertNotSame($oldHash, $newToken->token);
        $this->assertSame(hash('sha256', $newSecret), $newToken->token);
    }

    public function testUpdateModifiesDescriptionAndDisabled(): void
    {
        $user = User::factory()->admin()->create(['enabled' => 1]);
        $token = $user->createToken('Old Name');
        $pat = $token->accessToken;

        $response = $this->actingAs($user)->patchJson(route('api-access.update', $pat->id), [
            'description' => 'New Name',
            'disabled' => true,
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'ok',
                'description' => 'New Name',
                'disabled' => true,
                'status_label' => 'danger',
            ]);

        $pat->refresh();
        $this->assertSame('New Name', $pat->name);
        $this->assertNotNull($pat->expires_at);
        $this->assertTrue($pat->expires_at->isPast());
    }

    public function testUpdateModifiesExpirationDaysAndClearsExpiration(): void
    {
        $user = User::factory()->admin()->create(['enabled' => 1]);
        $token = $user->createToken('Expiring Token');
        $pat = $token->accessToken;

        // Set expiration to 30 days
        $response = $this->actingAs($user)->patchJson(route('api-access.update', $pat->id), [
            'disabled' => false,
            'expires_in' => 30,
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'ok',
                'disabled' => false,
                'status_label' => 'info',
            ]);

        $pat->refresh();
        $this->assertNotNull($pat->expires_at);
        $this->assertFalse($pat->expires_at->isPast());

        // Clear expiration (never expires)
        $response = $this->actingAs($user)->patchJson(route('api-access.update', $pat->id), [
            'disabled' => false,
            'expires_in' => 0,
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'ok',
                'disabled' => false,
                'status_label' => 'success',
            ]);

        $pat->refresh();
        $this->assertNull($pat->expires_at);
    }

    public function testDestroyDeletesToken(): void
    {
        $user = User::factory()->admin()->create(['enabled' => 1]);
        $token = $user->createToken('To Delete');
        $pat = $token->accessToken;

        $response = $this->actingAs($user)->delete(route('api-access.destroy', $pat->id));

        $response->assertRedirect(route('api-access.index'));
        $this->assertNull(PersonalAccessToken::find($pat->id));
    }

    public function testMigrationTransfersLegacyApiTokensToPersonalAccessTokens(): void
    {
        $user = User::factory()->admin()->create(['enabled' => 1]);
        $rawToken1 = bin2hex(random_bytes(16)); // 32 chars
        $rawToken2 = bin2hex(random_bytes(16));

        $migration = require database_path('migrations/2026_09_12_000000_migrate_api_tokens_to_sanctum.php');
        $migration->down(); // ensures api_tokens table exists

        DB::table('api_tokens')->insert([
            'user_id' => $user->user_id,
            'token_hash' => $rawToken1,
            'description' => 'Legacy unhashed 1',
            'disabled' => 0,
        ]);

        DB::table('api_tokens')->insert([
            'user_id' => $user->user_id,
            'token_hash' => $rawToken2,
            'description' => 'Legacy unhashed 2',
            'disabled' => 1,
        ]);

        $migration->up();

        $hash1 = hash('sha256', $rawToken1);
        $hash2 = hash('sha256', $rawToken2);

        $pat1 = DB::table('personal_access_tokens')->where('token', $hash1)->first();
        $pat2 = DB::table('personal_access_tokens')->where('token', $hash2)->first();

        $this->assertNotNull($pat1);
        $this->assertSame('Legacy unhashed 1', $pat1->name);
        $this->assertSame($user->user_id, (int) $pat1->tokenable_id);
        $this->assertNull($pat1->expires_at);

        $this->assertNotNull($pat2);
        $this->assertSame('Legacy unhashed 2', $pat2->name);
        $this->assertNotNull($pat2->expires_at);

        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('api_tokens'));
    }
}
