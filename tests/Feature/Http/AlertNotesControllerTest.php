<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Alert;
use App\Models\AlertFault;
use App\Models\AlertRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class AlertNotesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testFaultPageAndDashboardUseNamedNoteRoutes(): void
    {
        $this->actingAs($this->admin());

        foreach (['faults', 'dashboard.index'] as $page) {
            $this->get(route($page))
                ->assertOk()
                ->assertSee('id="alert-notes-form"', false)
                ->assertSee('show: ' . Js::from(route('fault.notes.show', '__FAULT_ID__'))->toHtml(), false)
                ->assertSee('update: ' . Js::from(route('fault.notes.update', '__FAULT_ID__'))->toHtml(), false);
        }
    }

    public function testReadUpdateAndClearNote(): void
    {
        $alert = Alert::factory()->create(['note' => 'Aggregate note']);
        $fault = $this->makeFault($alert, 'first');
        $original = $fault->refresh()->getRawOriginal();
        $originalAlert = $alert->refresh()->getRawOriginal();
        $note = "Check the link\nLoss & errors";

        $this->actingAs($this->admin())
            ->getJson(route('fault.notes.show', $fault))->assertOk()->assertJsonPath('note', 'Previous note');

        $url = route('fault.notes.update', $fault);
        $this->patch($url, ['note' => "  <b>Check the link</b>\nLoss & errors  "], ['Accept' => 'application/json'])
            ->assertOk()->assertJsonPath('note', $note);
        $this->assertSame(array_replace($original, ['note' => $note]), $fault->refresh()->getRawOriginal());
        $this->getJson(route('fault.notes.show', $fault))->assertOk()->assertJsonPath('note', $note);
        $this->patchJson($url, ['note' => $note])->assertOk()->assertJsonPath('note', $note);
        $this->patchJson($url, ['note' => ''])->assertOk()->assertJsonPath('note', '');
        $this->assertSame('', $fault->refresh()->note);
        $this->assertSame($originalAlert, $alert->refresh()->getRawOriginal());
    }

    public function testRoutesRequireAuthenticationAndAValidFault(): void
    {
        $fault = $this->makeFault(Alert::factory()->create(), 'first');

        $this->getJson(route('fault.notes.show', $fault))->assertUnauthorized();
        $this->patchJson(route('fault.notes.update', $fault), ['note' => 'Updated'])->assertUnauthorized();

        $this->actingAs($this->admin())
            ->getJson(route('fault.notes.show', 0))->assertNotFound();
        $this->patchJson(route('fault.notes.update', 0), ['note' => 'Updated'])->assertNotFound();
    }

    public function testRoutesRequireABackingAlertForTheSameDeviceAndRule(): void
    {
        $alert = Alert::factory()->create();
        $fault = $this->makeFault($alert, 'first');
        Alert::factory()->create(['rule_id' => $alert->rule_id]);
        Alert::factory()->create(['device_id' => $alert->device_id]);
        $alert->delete();

        $this->actingAs($this->admin())
            ->getJson(route('fault.notes.show', $fault))->assertForbidden();
        $this->patchJson(route('fault.notes.update', $fault), ['note' => 'Updated'])->assertForbidden();
        $this->assertSame('Previous note', $fault->refresh()->note);
    }

    public function testViewAndUpdateFollowAlertAndDevicePermissions(): void
    {
        $alert = Alert::factory()->create();
        $fault = $this->makeFault($alert, 'first');
        $otherFault = $this->makeFault(Alert::factory()->create(), 'first');
        $user = User::factory()->create(['enabled' => 1]);
        $user->devicesOwned()->attach($alert->device_id);
        $user->givePermissionTo(Permission::findOrCreate('alert.view'));

        $this->actingAs($user)
            ->getJson(route('fault.notes.show', $fault))->assertOk()->assertJsonPath('note', 'Previous note');
        $this->getJson(route('fault.notes.show', $otherFault))->assertForbidden();
        $this->patchJson(route('fault.notes.update', $fault), ['note' => 'Updated'])->assertForbidden();
        $this->assertSame('Previous note', $fault->refresh()->note);

        $user->givePermissionTo(Permission::findOrCreate('alert.update'));

        $this->patchJson(route('fault.notes.update', $otherFault), ['note' => 'Updated'])->assertForbidden();
        $this->patchJson(route('fault.notes.update', $fault), ['note' => ['invalid']])
            ->assertUnprocessable()->assertJsonValidationErrors('note');
        $this->patchJson(route('fault.notes.update', $fault), ['note' => 'Updated'])->assertOk();
        $this->assertSame('Updated', $fault->refresh()->note);
        $this->assertSame('Previous note', $otherFault->refresh()->note);
    }

    #[DataProvider('notificationModes')]
    public function testUpdateScopeFollowsRuleNotificationMode(bool $perEntity, int $maxEntities, bool $grouped): void
    {
        $rule = AlertRule::factory()->create(['notify_per_entity' => $perEntity, 'max_entities' => $maxEntities]);
        $alert = Alert::factory()->create(['rule_id' => $rule->id]);
        $fault = $this->makeFault($alert, 'first');
        $sibling = $this->makeFault($alert, 'second');
        $closed = $this->makeFault($alert, 'closed', open: false);
        $otherDevice = $this->makeFault(Alert::factory()->create(['rule_id' => $rule->id]), 'first');
        $otherRule = $this->makeFault(Alert::factory()->create(['device_id' => $alert->device_id]), 'first');

        $this->actingAs($this->admin())
            ->patchJson(route('fault.notes.update', $fault), ['note' => 'Updated'])->assertOk();

        $this->assertSame('Updated', $fault->refresh()->note);
        $this->assertSame($grouped ? 'Updated' : 'Previous note', $sibling->refresh()->note);
        $this->assertSame('Previous note', $closed->refresh()->note);
        $this->assertSame('Previous note', $otherDevice->refresh()->note);
        $this->assertSame('Previous note', $otherRule->refresh()->note);
    }

    /**
     * @return array<string, array{bool, int, bool}>
     */
    public static function notificationModes(): array
    {
        return [
            'grouped rule' => [false, 2, true],
            'per entity within limit' => [true, 2, false],
            'per entity over limit' => [true, 1, true],
        ];
    }

    public function testGroupedUpdateFailsWhenAllFaultsAreClosed(): void
    {
        $rule = AlertRule::factory()->create(['notify_per_entity' => false]);
        $fault = $this->makeFault(Alert::factory()->create(['rule_id' => $rule->id]), 'closed', open: false);

        $this->actingAs($this->admin())
            ->patchJson(route('fault.notes.update', $fault), ['note' => 'Updated'])->assertConflict();
        $this->assertSame('Previous note', $fault->refresh()->note);
    }

    private function makeFault(Alert $alert, string $entityKey, bool $open = true): AlertFault
    {
        return AlertFault::create([
            'rule_id' => $alert->rule_id,
            'device_id' => $alert->device_id,
            'entity_key' => $entityKey,
            'open' => (int) $open,
            'state' => $open ? AlertState::ACKNOWLEDGED : AlertState::RECOVERED,
            'alerted' => 1,
            'note' => 'Previous note',
            'info' => ['until_clear' => true],
            'details' => ['rule' => [['name' => $entityKey]]],
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['enabled' => 1])->assignRole(Role::findOrCreate('admin'));
    }
}
