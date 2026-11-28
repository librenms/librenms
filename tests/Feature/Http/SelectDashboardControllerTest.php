<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;

final class SelectDashboardControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testSharedDashboardLabelReflectsUpdatePermission(): void
    {
        $owner = User::factory()->create(['enabled' => 1]);
        $viewer = User::factory()->create(['enabled' => 1]);
        $dashboard = Dashboard::create([
            'user_id' => $owner->user_id,
            'dashboard_name' => 'Shared dashboard',
            'access' => 2,
        ]);
        $url = route('ajax.select.dashboard', ['id' => $dashboard->dashboard_id]);
        $label = "$owner->username: $dashboard->dashboard_name";

        $this->actingAs($viewer)->getJson($url)
            ->assertOk()
            ->assertJsonPath('results.0.text', "$label (read-only)");

        $this->actingAs($owner)->getJson($url)
            ->assertOk()
            ->assertJsonPath('results.0.text', "$label (read-write)");

        $viewer->givePermissionTo(Permission::findOrCreate('dashboard.update'));

        $this->actingAs($viewer)->getJson($url)
            ->assertOk()
            ->assertJsonPath('results.0.text', "$label (read-write)");
    }
}
