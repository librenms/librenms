<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Dashboard;
use App\Models\User;
use Dom\HTMLDocument;
use Illuminate\Testing\TestResponse;
use LibreNMS\Tests\InMemoryDbTestCase;
use PHPUnit\Framework\Attributes\TestWith;
use Spatie\Permission\Models\Permission;

final class DashboardCopyTest extends InMemoryDbTestCase
{
    #[TestWith([])]
    #[TestWith(['user.view'])]
    #[TestWith(['dashboard.copy'])]
    public function testCopySelectOnlyIncludesSelfWithoutBothGlobalPermissions(?string $permission = null): void
    {
        $user = User::factory()->create(['enabled' => true]);
        if ($permission !== null) {
            $user->givePermissionTo(Permission::findOrCreate($permission));
        }
        $owner = User::factory()->create();
        $dashboard = $this->dashboard($owner, 1);

        $response = $this->actingAs($user)->get(route('dashboard.show', $dashboard))->assertOk();
        $this->assertSame([-1 => __('dashboard.buttons.copy_to'), $user->user_id => $user->username], $this->copyOptions($response));
    }

    public function testAdminCanSelectSelfAndOtherUsers(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true, 'username' => 'zed']);
        $owner = User::factory()->create(['username' => 'alex']);
        $dashboard = $this->dashboard($owner, 1);

        $response = $this->actingAs($user)->get(route('dashboard.show', $dashboard))->assertOk();
        $this->assertSame([
            -1 => __('dashboard.buttons.copy_to'),
            $owner->user_id => $owner->username,
            $user->user_id => $user->username,
        ], $this->copyOptions($response));

        $this->postJson(route('dashboard.copy', $dashboard), ['target_user_id' => $owner->user_id])->assertOk();
        $this->assertDatabaseHas('dashboards', ['user_id' => $owner->user_id, 'dashboard_name' => 'Original_zed']);
    }

    public function testUserCanCopyViewableDashboardOnlyToSelf(): void
    {
        $user = User::factory()->create(['enabled' => true]);
        $owner = User::factory()->create();
        $shared = $this->dashboard($owner, 1);
        $private = $this->dashboard($owner, 0);

        $this->actingAs($user)
            ->postJson(route('dashboard.copy', $shared), ['target_user_id' => (string) $user->user_id])
            ->assertOk()
            ->assertJsonPath('status', 'ok');
        $this->assertDatabaseHas('dashboards', ['user_id' => $user->user_id, 'dashboard_name' => 'Original_' . $user->username]);

        $this->postJson(route('dashboard.copy', $shared), ['target_user_id' => $owner->user_id])->assertForbidden();
        $this->postJson(route('dashboard.copy', $shared), ['target_user_id' => $owner->user_id + 1])->assertForbidden();
        $this->postJson(route('dashboard.copy', $private), ['target_user_id' => $user->user_id])->assertForbidden();
    }

    private function dashboard(User $owner, int $access): Dashboard
    {
        return Dashboard::create(['user_id' => $owner->user_id, 'dashboard_name' => 'Original', 'access' => $access]);
    }

    /**
     * @param  TestResponse<\Symfony\Component\HttpFoundation\Response>  $response
     * @return array<int, string>
     */
    private function copyOptions(TestResponse $response): array
    {
        $page = HTMLDocument::createFromString((string) $response->getContent(), LIBXML_NOERROR);
        $options = [];
        foreach ($page->querySelectorAll('#dashboard_copy_target option') as $option) {
            $options[(int) $option->getAttribute('value')] = trim($option->textContent);
        }

        return $options;
    }
}
