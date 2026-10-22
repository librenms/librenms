<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
}
