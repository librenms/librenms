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

    public function testEditPageSavesRulesChosenInSelect2(): void
    {
        $user = $this->admin();
        $template = AlertTemplate::factory()->create(['name' => 'Custom Template', 'template' => 'body']);
        $rule = AlertRule::factory()->create(['name' => 'Free Rule']);
        $usedRule = AlertRule::factory()->create(['name' => 'Unavailable Rule']);
        AlertTemplate::factory()->create()->map()->create(['alert_rule_id' => $usedRule->id]);

        $this->browse(function (Browser $browser) use ($user, $template): void {
            $browser->loginAs($user)
                ->visit('/alert-templates/' . $template->id . '/edit')
                ->waitFor('#alert-template-form .select2-selection')
                ->click('#alert-template-form .select2-selection')
                ->waitFor('.select2-results__option[aria-disabled="true"]')
                ->assertSeeIn('.select2-results__option[aria-disabled="true"]', 'Used in template')
                ->click('.select2-results__option[aria-disabled="true"]')
                ->clickAtXPath('//li[contains(@class, "select2-results__option") and text()="Free Rule"]')
                ->press('Save')
                ->waitForLocation('/alert-templates');
        });

        $this->assertSame([$rule->id], $template->map()->pluck('alert_rule_id')->all());
    }

    public function testConvertReplacesLegacySyntaxBeforeSaving(): void
    {
        $user = $this->admin();
        $template = AlertTemplate::factory()->create(['template' => '{if %state}up{/if} %hostname', 'title' => '%title']);

        $this->browse(function (Browser $browser) use ($user, $template): void {
            $browser->loginAs($user)
                ->visit('/alert-templates/' . $template->id . '/edit')
                ->assertSee('legacy template')
                ->press('Convert template')
                ->waitUntilMissingText('legacy template')
                ->assertInputValue('#template', ' @if ($alert->state) up @endif  {{ $alert->hostname }}')
                ->assertInputValue('#title', '{{ $alert->title }}')
                ->press('Save')
                ->waitForLocation('/alert-templates');
        });

        $this->assertSame('@if ($alert->state) up @endif  {{ $alert->hostname }}', $template->refresh()->template);
        $this->assertSame('{{ $alert->title }}', $template->title);
    }

    private function admin(): User
    {
        Role::findOrCreate('admin');
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('admin');

        return $user;
    }
}
