<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\AlertRule;
use App\Models\AlertTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
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
                'name' => 'New Template',
                'template' => '{{ $alert->title }}',
                'title' => 'Alert {{ $alert->hostname }}',
                'rules' => [$rule->id],
            ])
            ->assertCreated();

        $template = AlertTemplate::where('name', 'New Template')->sole();
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

    public function testShowUpdateAndDestroy(): void
    {
        $template = AlertTemplate::factory()->create(['template' => '{if %x}a{/if}']);
        $rule = AlertRule::factory()->create();

        $this->actingAs($this->admin())
            ->getJson(route('alert-templates.show', $template))
            ->assertOk()
            ->assertJsonPath('legacy', true);

        $this->actingAs($this->admin())
            ->putJson(route('alert-templates.update', $template), ['name' => 'Renamed', 'template' => 'body', 'rules' => [$rule->id]])
            ->assertOk();
        $this->assertSame('Renamed', $template->refresh()->name);
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
