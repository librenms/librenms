<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Http\Middleware\CheckInstalled;
use App\Models\ServiceTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

class ServiceTemplateApplyTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testApplyingTemplateWithoutDeviceGroupsSucceeds(): void
    {
        $this->withoutMiddleware(CheckInstalled::class);

        $admin = User::factory()
            ->admin()
            ->create(['enabled' => 1]);

        $template = ServiceTemplate::factory()->create([
            'type' => 'static',
        ]);

        // Reproduce a template with no assigned device groups.
        $this->assertCount(0, $template->groups);

        $response = $this->actingAs($admin)
            ->post(route('services.templates.apply', [
                'template' => $template,
            ]));

        $response->assertOk();

    }
}
