<?php

namespace LibreNMS\Tests\Feature;

use App\Models\ApiToken;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

final class EditLocationApiTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testEditLocationPersistsTheNewCoordinates(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = ApiToken::generateToken($user);
        $location = Location::factory()->create(['lat' => 1.0, 'lng' => 2.0]);

        $this->json('PATCH', "/api/v0/locations/{$location->id}", [
            'lat' => 51.5,
            'lng' => -0.12,
        ], ['X-Auth-Token' => $token->token_hash])
            ->assertStatus(201)
            ->assertJson(['status' => 'ok']);

        $location->refresh();

        $this->assertEquals(51.5, $location->lat, 'lat must be written to the database');
        $this->assertEquals(-0.12, $location->lng, 'lng must be written to the database');
    }
}
