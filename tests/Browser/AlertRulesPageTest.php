<?php

namespace LibreNMS\Tests\Browser;

use App\Models\AlertRule;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use LibreNMS\Alerting\AlertRuleDefinitions;
use LibreNMS\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('browser')]
final class AlertRulesPageTest extends DuskTestCase
{
    use DatabaseTruncation;

    /** @var list<string> */
    protected array $connectionsToTruncate = ['testing', 'testing_persistent'];

    public function testEditLinkOpensAPageAndSubmitsTheRule(): void
    {
        $rule = AlertRule::factory()->create([
            'name' => 'Original rule',
            'extra' => ['options' => ['override_query' => true]],
        ]);

        $this->browse(function (Browser $browser) use ($rule): void {
            $editLink = "a[href$='/alert-rule/{$rule->id}/edit']";

            $browser->loginAs($this->browserUser('admin'))->visit('/alert-rules')
                ->waitFor($editLink)
                ->assertMissing('#alert-rule-form')
                ->click($editLink)
                ->waitForLocation("/alert-rule/{$rule->id}/edit")
                ->waitUntilEnabled('#btn-save')
                ->assertInputValue('#rule_name', 'Original rule')
                ->type('#rule_name', 'Updated rule')
                ->press('Save Rule')
                ->waitForLocation('/alert-rules')
                ->assertSee('Updated rule');
        });

        $this->assertSame('Updated rule', $rule->refresh()->name);
    }

    public function testCollectionLinkOpensACreatePageWithTheSelectedTemplate(): void
    {
        $id = array_search('Device rebooted', array_column(AlertRuleDefinitions::all(), 'name'), true);
        $this->assertIsInt($id);

        $this->browse(function (Browser $browser) use ($id): void {
            $browser->loginAs($this->browserUser('admin'))->visit('/alert-rules')
                ->clickLink('Create rule from collection')
                ->waitForLocation('/alert-rule/create')
                ->waitFor('#search_rule_modal.in')
                ->waitUntil("getComputedStyle(document.querySelector('#search_rule_modal')).opacity === '1'")
                ->click("#search_rule_modal a[href*='template={$id}']")
                ->waitUntil("new URL(window.location.href).searchParams.get('template') === '{$id}'")
                ->waitUntilEnabled('#btn-save')
                ->assertInputValue('#rule_name', 'Device rebooted')
                ->assertInputValue('template_id', (string) $id)
                ->type('#rule_name', 'Imported reboot rule')
                ->press('Save Rule')
                ->waitForLocation('/alert-rules')
                ->assertSee('Imported reboot rule');
        });

        $rule = AlertRule::where('name', 'Imported reboot rule')->firstOrFail();
        $this->assertSame(1, $rule->extra['count']);
    }
}
