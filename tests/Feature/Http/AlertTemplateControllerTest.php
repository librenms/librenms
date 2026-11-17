<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\AlertRule;
use App\Models\AlertTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
            ->assertSee('Attached Rule');
    }

    public function testStoreCreatesTemplateWithRules(): void
    {
        $rule = AlertRule::factory()->create();

        $this->actingAs($this->admin())
            ->postJson(route('alert-templates.store'), [
                'name' => 'New & Original < 10',
                'template' => '{{ $alert->title }}',
                'title' => 'Alert {{ $alert->hostname }}',
                'rules' => [$rule->id],
            ])
            ->assertCreated();

        $template = AlertTemplate::where('name', 'New & Original < 10')->sole();
        $this->assertSame([$rule->id], $template->map()->pluck('alert_rule_id')->all());
    }

    public function testStoreRejectsInvalidTemplateAndReservedName(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('alert-templates.store'), ['name' => 'Broken', 'template' => '@if(true)'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('template');

        $this->actingAs($this->admin())
            ->postJson(route('alert-templates.store'), ['name' => 'Default Alert Template', 'template' => 'x'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

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
            ->json($method, $url, ['name' => $name, 'template' => 'Updated body'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');

        $this->assertSame($templateCount, AlertTemplate::count());
        $this->assertSame('Original', $template->refresh()->name);
        $this->assertSame('Original body', $template->template);
    }

    public function testDefaultTemplateRejectsHtmlName(): void
    {
        $default = AlertTemplate::firstOrCreate(['name' => 'Default Alert Template'], ['template' => 'x']);
        $originalBody = $default->template;

        $this->actingAs($this->admin())
            ->putJson(route('alert-templates.update', $default), ['name' => '<b>Default Alert Template</b>', 'template' => 'Updated body'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name' => 'The template name must not contain HTML tags.']);

        $this->assertSame('Default Alert Template', $default->refresh()->name);
        $this->assertSame($originalBody, $default->template);
    }

    public function testShowUpdateAndDestroy(): void
    {
        $template = AlertTemplate::factory()->create(['template' => '{if %x}a{/if}']);
        $rule = AlertRule::factory()->create();

        $this->actingAs($this->admin())
            ->getJson(route('alert-templates.show', $template))
            ->assertOk()
            ->assertJsonPath('legacy', true);

        $this->actingAs($this->admin())
            ->putJson(route('alert-templates.update', $template), ['name' => 'Renamed & Updated < 10', 'template' => 'body', 'rules' => [$rule->id]])
            ->assertOk();
        $this->assertSame('Renamed & Updated < 10', $template->refresh()->name);
        $this->assertSame([$rule->id], $template->map()->pluck('alert_rule_id')->all());

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
            ->putJson(route('alert-templates.update', $default), ['name' => 'Other', 'template' => 'x'])
            ->assertUnprocessable();

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

        $this->actingAs($user)->postJson(route('alert-templates.store'), ['name' => 'x', 'template' => 'x'])->assertForbidden();
        $this->actingAs($user)->putJson(route('alert-templates.update', $template), ['name' => 'x', 'template' => 'x'])->assertForbidden();
        $this->actingAs($user)->deleteJson(route('alert-templates.destroy', $template))->assertForbidden();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
