<?php

namespace LibreNMS\Tests\Feature\Commands;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

class ApiTokenCommandTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function testCreateToken(): void
    {
        $user = User::factory()->admin()->create();

        $this->artisan('api:token', [
            'action' => 'create',
            'username' => $user->username,
            '--token-name' => 'test-token',
        ])
            ->expectsOutput('Token created successfully.')
            ->assertExitCode(0);

        $this->assertCount(1, $user->tokens);
        $this->assertEquals('test-token', $user->tokens->first()->name);
    }

    public function testCreateTokenDefaultName(): void
    {
        $user = User::factory()->admin()->create();

        $this->artisan('api:token', [
            'action' => 'create',
            'username' => $user->username,
        ])->assertExitCode(0);

        $this->assertEquals('api-token', $user->tokens()->first()->name);
    }

    public function testCreateTokenUserNotFound(): void
    {
        $this->artisan('api:token', [
            'action' => 'create',
            'username' => 'nonexistent-user-xyz',
        ])
            ->expectsOutput("User 'nonexistent-user-xyz' not found.")
            ->assertExitCode(1);
    }

    public function testListTokens(): void
    {
        $user = User::factory()->admin()->create();
        $user->createToken('token-one');
        $user->createToken('token-two');

        $this->artisan('api:token', [
            'action' => 'list',
            'username' => $user->username,
        ])->assertExitCode(0);
    }

    public function testListTokensEmpty(): void
    {
        $user = User::factory()->admin()->create();

        $this->artisan('api:token', [
            'action' => 'list',
            'username' => $user->username,
        ])
            ->expectsOutput("No tokens found for user '{$user->username}'.")
            ->assertExitCode(0);
    }

    public function testRevokeToken(): void
    {
        $user = User::factory()->admin()->create();
        $user->createToken('to-revoke');
        $tokenId = $user->tokens->first()->id;

        $this->artisan('api:token', [
            'action' => 'revoke',
            'username' => $user->username,
            '--token-id' => $tokenId,
        ])
            ->expectsOutput("Token 'to-revoke' (ID: {$tokenId}) revoked.")
            ->assertExitCode(0);

        $this->assertCount(0, $user->tokens()->get());
    }

    public function testRevokeTokenMissingId(): void
    {
        $user = User::factory()->admin()->create();

        $this->artisan('api:token', [
            'action' => 'revoke',
            'username' => $user->username,
        ])
            ->expectsOutput('The --token-id option is required for the revoke action.')
            ->assertExitCode(1);
    }

    public function testRevokeTokenNotFound(): void
    {
        $user = User::factory()->admin()->create();

        $this->artisan('api:token', [
            'action' => 'revoke',
            'username' => $user->username,
            '--token-id' => 99999,
        ])
            ->expectsOutput("Token ID 99999 not found for user '{$user->username}'.")
            ->assertExitCode(1);
    }

    public function testInvalidAction(): void
    {
        $user = User::factory()->admin()->create();

        $this->artisan('api:token', [
            'action' => 'invalid',
            'username' => $user->username,
        ])
            ->expectsOutput("Unknown action 'invalid'. Use: create, list, revoke")
            ->assertExitCode(1);
    }
}
