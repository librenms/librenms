<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\User;
use App\Models\UserPref;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;

class TrafficPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function testPreferenceIsValidatedPersistedAndIsolatedPerUser(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\CheckInstalled::class);
        $user = User::factory()->create(['enabled' => 1]);
        $other = User::factory()->create(['enabled' => 1]);
        $this->actingAs($user);
        foreach ([1, 0] as $value) {
            $this->postJson(route('preferences.store'), ['pref' => 'traffic_same_axis', 'value' => $value])
                ->assertOk()->assertJsonPath('status', 'success');
            $this->assertEquals($value, UserPref::getPref($user, 'traffic_same_axis'));
            $this->assertNull(UserPref::getPref($other, 'traffic_same_axis'));
        }
        $this->postJson(route('preferences.store'), ['pref' => 'traffic_same_axis', 'value' => 'invalid'])
            ->assertUnprocessable();
        $this->assertEquals(0, UserPref::getPref($user, 'traffic_same_axis'));
    }
}
