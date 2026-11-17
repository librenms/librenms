<?php

namespace LibreNMS\Tests\Browser;

use App\Models\AlertRule;
use App\Models\AlertTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use LibreNMS\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Permission\Models\Role;

#[Group('browser')]
final class AlertTemplatesPageTest extends DuskTestCase
{
    use DatabaseTruncation;

    /** @var list<string> */
    protected array $connectionsToTruncate = ['testing', 'testing_persistent'];

    public function testCustomTemplateNameRemainsEditableAfterEnteringDefaultName(): void
    {
        $user = $this->admin();
        $template = AlertTemplate::factory()->create(['name' => 'Custom Template']);

        $this->browse(function (Browser $browser) use ($user, $template): void {
            $browser->loginAs($user)
                ->visit('/alert-templates')
                ->waitFor('button[x-on\:click="edit(' . $template->id . ')"]')
                ->click('button[x-on\:click="edit(' . $template->id . ')"]')
                ->waitFor('#template-name')
                ->type('#template-name', 'Default Alert Template')
                ->assertAttributeMissing('#template-name', 'readonly')
                ->type('#template-name', 'Renamed Template')
                ->assertInputValue('#template-name', 'Renamed Template');
        });
    }

    public function testClickingDisabledRuleKeepsUnsavedEditorOpen(): void
    {
        $user = $this->admin();
        $template = AlertTemplate::factory()->create(['name' => 'Custom Template']);
        $usedTemplate = AlertTemplate::factory()->create();
        $rule = AlertRule::factory()->create(['name' => 'Unavailable Rule']);
        $usedTemplate->map()->create(['alert_rule_id' => $rule->id]);

        $this->browse(function (Browser $browser) use ($user, $template): void {
            $browser->loginAs($user)
                ->visit('/alert-templates')
                ->click('button[x-on\:click="edit(' . $template->id . ')"]')
                ->waitFor('#template-name')
                ->type('#template-name', 'Unsaved Name')
                ->waitFor('#template-form .select2-selection')
                ->click('#template-form .select2-selection')
                ->waitFor('.select2-results__option[aria-disabled="true"]')
                ->click('.select2-results__option[aria-disabled="true"]')
                ->assertVisible('#template-name')
                ->assertInputValue('#template-name', 'Unsaved Name');
        });

        $this->assertSame('Custom Template', $template->refresh()->name);
    }

    private function admin(): User
    {
        Role::findOrCreate('admin');
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('admin');

        return $user;
    }
}
