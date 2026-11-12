<?php

namespace LibreNMS\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;

final class RoleFormTest extends TestCase
{
    use RefreshDatabase;

    public function testRiskyPermissionsAreMarked(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);

        $this->actingAs($user)
            ->get(route('roles.create'))
            ->assertOk()
            ->assertSee(__('permissions.alert-template.update.label'))
            ->assertSee(__('permissions.rbac.risky'))
            ->assertSee(__('permissions.alert-template.update.risk'))
            ->assertSee(__('permissions.sensor.view.label'))
            ->assertDontSee('permissions.sensor');
    }
}
