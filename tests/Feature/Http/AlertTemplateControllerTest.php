<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\AlertRule;
use App\Models\AlertTemplate;
use App\Models\User;
use Dom\HTMLDocument;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestWith;
use Spatie\Permission\Models\Role;

final class AlertTemplateControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    public function testIndexListsTemplatesAndTheirRules(): void
    {
        $rule = AlertRule::factory()->create(['name' => 'Attached Rule']);
        $template = AlertTemplate::factory()->create(['name' => 'Escaped <b>Template</b>']);
        $template->map()->create(['alert_rule_id' => $rule->id]);

        $this->actingAs($this->admin())
            ->get(route('alert-templates.index'))
            ->assertOk()
            ->assertSee('Escaped &lt;b&gt;Template&lt;/b&gt;', false)
            ->assertSee('href="' . route('alert-templates.edit', $template) . '"', false)
            ->assertSee('href="' . route('alert-templates.create') . '"', false)
            ->assertSee('Attached Rule');
    }

    public function testStoreCreatesTemplateWithRules(): void
    {
        $rule = AlertRule::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('alert-templates.store'), [
                'name' => 'New & Original < 10',
                'template' => '{{ $alert->title }}',
                'title' => 'Alert {{ $alert->hostname }}',
                'rules' => [$rule->id],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('alert-templates.index'));

        $template = AlertTemplate::where('name', 'New & Original < 10')->sole();
        $this->assertSame([$rule->id], $template->map()->pluck('alert_rule_id')->all());
    }

    public function testStoreRejectsInvalidTemplateAndReservedName(): void
    {
        $this->actingAs($this->admin())
            ->from(route('alert-templates.create'))
            ->post(route('alert-templates.store'), ['name' => 'Broken', 'template' => '@if(true)'])
            ->assertRedirect(route('alert-templates.create'))
            ->assertSessionHasErrors('template');

        $this->actingAs($this->admin())
            ->post(route('alert-templates.store'), ['name' => 'Default Alert Template', 'template' => 'x'])
            ->assertSessionHasErrors(['name' => 'This template name is reserved!']);

        $this->assertFalse(AlertTemplate::where('name', 'Broken')->exists());
    }

    /**
     * @param  string|list<string>  $name
     */
    #[TestWith(['POST', '<b>Custom Template</b>'])]
    #[TestWith(['POST', '<!-- comment -->Custom Template'])]
    #[TestWith(['POST', '<b>Default Alert Template</b>'])]
    #[TestWith(['POST', '<b></b>'])]
    #[TestWith(['POST', '<b> </b>'])]
    #[TestWith(['POST', ['invalid']])]
    #[TestWith(['PUT', '<b>Custom Template</b>'])]
    #[TestWith(['PUT', '<!-- comment -->Custom Template'])]
    #[TestWith(['PUT', '<b>Default Alert Template</b>'])]
    #[TestWith(['PUT', '<b></b>'])]
    #[TestWith(['PUT', '<b> </b>'])]
    #[TestWith(['PUT', ['invalid']])]
    public function testSaveRejectsInvalidName(string $method, string|array $name): void
    {
        $template = AlertTemplate::factory()->create(['name' => 'Original', 'template' => 'Original body']);
        $templateCount = AlertTemplate::count();
        $url = $method === 'POST' ? route('alert-templates.store') : route('alert-templates.update', $template);

        $this->actingAs($this->admin())
            ->call($method, $url, ['name' => $name, 'template' => 'Updated body'])
            ->assertSessionHasErrors('name');

        $this->assertSame($templateCount, AlertTemplate::count());
        $this->assertSame('Original', $template->refresh()->name);
        $this->assertSame('Original body', $template->template);
    }

    public function testDefaultTemplateRejectsHtmlName(): void
    {
        $default = AlertTemplate::firstOrCreate(['name' => 'Default Alert Template'], ['template' => 'x']);
        $originalBody = $default->template;

        $this->actingAs($this->admin())
            ->put(route('alert-templates.update', $default), ['name' => '<b>Default Alert Template</b>', 'template' => 'Updated body'])
            ->assertSessionHasErrors(['name' => 'The template name must not contain HTML tags.']);

        $this->assertSame('Default Alert Template', $default->refresh()->name);
        $this->assertSame($originalBody, $default->template);
    }

    public function testCreateShowsRulesUsedByOtherTemplatesAsDisabled(): void
    {
        $free = AlertRule::factory()->create(['name' => 'Free Rule']);
        $used = AlertRule::factory()->create(['name' => 'Used Rule']);
        AlertTemplate::factory()->create(['name' => 'Other <i>Template</i>'])->map()->create(['alert_rule_id' => $used->id]);

        $page = $this->dom($this->actingAs($this->admin())->get(route('alert-templates.create'))->assertOk()->assertDontSee('Convert template'));

        $this->assertSame(route('alert-templates.store'), $page->getElementById('alert-template-form')->getAttribute('action'));
        $freeOption = $page->querySelector('#rules option[value="' . $free->id . '"]');
        $this->assertSame('Free Rule', $freeOption->textContent);
        $this->assertFalse($freeOption->hasAttribute('disabled'));
        $usedOption = $page->querySelector('#rules option[value="' . $used->id . '"]');
        $this->assertTrue($usedOption->hasAttribute('disabled'));
        $this->assertSame('Other <i>Template</i>', $usedOption->getAttribute('data-template'));
    }

    public function testEditShowsTemplateWithItsRulesSelected(): void
    {
        $template = AlertTemplate::factory()->create(['name' => 'Editable', 'template' => '{if %x}a{/if}', 'title' => 'Legacy %title']);
        $rule = AlertRule::factory()->create(['name' => 'Attached Rule']);
        $template->map()->create(['alert_rule_id' => $rule->id]);

        $page = $this->dom($this->actingAs($this->admin())->get(route('alert-templates.edit', $template))->assertOk()->assertSee('Convert template'));

        $this->assertSame(route('alert-templates.update', $template), $page->getElementById('alert-template-form')->getAttribute('action'));
        $this->assertSame('Editable', $page->getElementById('name')->getAttribute('value'));
        $this->assertFalse($page->getElementById('name')->hasAttribute('readonly'));
        $this->assertSame('{if %x}a{/if}', $page->getElementById('template')->textContent);
        $this->assertSame('Legacy %title', $page->getElementById('title')->getAttribute('value'));
        $ruleOption = $page->querySelector('#rules option[value="' . $rule->id . '"]');
        $this->assertTrue($ruleOption->hasAttribute('selected'));
        $this->assertFalse($ruleOption->hasAttribute('disabled'));
    }

    public function testEditDefaultTemplateNameIsReadonly(): void
    {
        $default = AlertTemplate::firstOrCreate(['name' => 'Default Alert Template'], ['template' => 'x']);

        $page = $this->dom($this->actingAs($this->admin())->get(route('alert-templates.edit', $default))->assertOk());

        $this->assertTrue($page->getElementById('name')->hasAttribute('readonly'));
    }

    public function testFailedUpdateKeepsInput(): void
    {
        $template = AlertTemplate::factory()->create(['name' => 'Original', 'template' => 'Original body']);
        $rule = AlertRule::factory()->create();

        $page = $this->dom($this->actingAs($this->admin())
            ->from(route('alert-templates.edit', $template))
            ->followingRedirects()
            ->put(route('alert-templates.update', $template), ['name' => 'Changed', 'template' => '@if(true) unsaved', 'rules' => [$rule->id]])
            ->assertOk()
            ->assertSee('Template failed to be parsed'));

        $this->assertSame('Changed', $page->getElementById('name')->getAttribute('value'));
        $this->assertSame('@if(true) unsaved', $page->getElementById('template')->textContent);
        $this->assertTrue($page->querySelector('#rules option[value="' . $rule->id . '"]')->hasAttribute('selected'));

        $this->assertSame('Original', $template->refresh()->name);
    }

    public function testFailedUpdateKeepsRemovedRulesUnselected(): void
    {
        $template = AlertTemplate::factory()->create(['name' => 'Original', 'template' => 'Original body']);
        $rule = AlertRule::factory()->create();
        $template->map()->create(['alert_rule_id' => $rule->id]);

        $page = $this->dom($this->actingAs($this->admin())
            ->from(route('alert-templates.edit', $template))
            ->followingRedirects()
            ->put(route('alert-templates.update', $template), ['name' => 'Original', 'template' => '@if(true) unsaved'])
            ->assertOk()
            ->assertSee('Template failed to be parsed'));

        $this->assertFalse($page->querySelector('#rules option[value="' . $rule->id . '"]')->hasAttribute('selected'));
        $this->assertSame([$rule->id], $template->map()->pluck('alert_rule_id')->all());
    }

    public function testUpdateAndDestroy(): void
    {
        $template = AlertTemplate::factory()->create();
        $oldRule = AlertRule::factory()->create();
        $rule = AlertRule::factory()->create();
        $template->map()->create(['alert_rule_id' => $oldRule->id]);

        $this->actingAs($this->admin())
            ->put(route('alert-templates.update', $template), ['name' => 'Renamed & Updated < 10', 'template' => 'body', 'rules' => [$rule->id]])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('alert-templates.index'));
        $this->assertSame('Renamed & Updated < 10', $template->refresh()->name);
        $this->assertSame([$rule->id], $template->map()->pluck('alert_rule_id')->all());

        $this->actingAs($this->admin())
            ->put(route('alert-templates.update', $template), ['name' => 'Renamed', 'template' => 'body'])
            ->assertSessionHasNoErrors();
        $this->assertSame(0, $template->map()->count());

        $this->actingAs($this->admin())
            ->deleteJson(route('alert-templates.destroy', $template))
            ->assertOk();
        $this->assertModelMissing($template);
        $this->assertSame(0, $template->map()->count());
    }

    public function testDefaultTemplateCannotBeRenamedOrDeleted(): void
    {
        $default = AlertTemplate::firstOrCreate(['name' => 'Default Alert Template'], ['template' => 'x']);

        $this->actingAs($this->admin())
            ->put(route('alert-templates.update', $default), ['name' => 'Other', 'template' => 'x'])
            ->assertSessionHasErrors('name');
        $this->assertSame('Default Alert Template', $default->refresh()->name);

        $this->actingAs($this->admin())
            ->deleteJson(route('alert-templates.destroy', $default))
            ->assertUnprocessable();
    }

    public function testConvertLegacySyntax(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('alert-templates.convert'), ['template' => '{if %state}up{/if} %hostname', 'title' => '%title'])
            ->assertOk()
            ->assertJsonPath('template', ' @if ($alert->state) up @endif  {{ $alert->hostname }}')
            ->assertJsonPath('title', '{{ $alert->title }}');
    }

    public function testUserCannotModifyTemplates(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $template = AlertTemplate::factory()->create();

        $this->actingAs($user)->get(route('alert-templates.create'))->assertForbidden();
        $this->actingAs($user)->get(route('alert-templates.edit', $template))->assertForbidden();
        $this->actingAs($user)->post(route('alert-templates.store'), ['name' => 'x', 'template' => 'x'])->assertForbidden();
        $this->actingAs($user)->put(route('alert-templates.update', $template), ['name' => 'x', 'template' => 'x'])->assertForbidden();
        $this->actingAs($user)->deleteJson(route('alert-templates.destroy', $template))->assertForbidden();
    }

    /**
     * @param  TestResponse<\Symfony\Component\HttpFoundation\Response>  $response
     */
    private function dom(TestResponse $response): HTMLDocument
    {
        return HTMLDocument::createFromString((string) $response->getContent(), LIBXML_NOERROR);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
