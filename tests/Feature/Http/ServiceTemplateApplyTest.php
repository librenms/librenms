<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Http\Middleware\CheckInstalled;
use App\Models\Device;
use App\Models\Service;
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

        // A service on an unassigned device should be removed.
        $device = Device::factory()->create();

        $service = Service::factory()->for($device)->create([
            'service_template_id' => $template->id,
        ]);

        $this->assertDatabaseHas('services', [
            'service_id' => $service->service_id,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('services.templates.apply', [
                'template' => $template,
            ]));

        $response->assertOk();

        $this->assertDatabaseMissing('services', [
            'service_id' => $service->service_id,
        ]);

    }
}
