<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class ServicesTabTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function testAuthorizedUserCanRenderServicesTab(): void
    {
        $device = Device::factory()->create();
        Service::factory()->for($device)->create([
            'service_type' => 'http',
            'service_desc' => 'HTTP Web Server Check',
            'service_ip' => '192.168.1.1',
            'service_status' => 0,
            'service_changed' => time() - 3 * 86400,
            'service_checked' => time() - 300,
            'service_message' => 'HTTP OK: 200 OK - 0.005 second response time',
            'service_ignore' => 0,
            'service_disabled' => 0,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'services']))
            ->assertOk()
            ->assertSee('HTTP Web Server Check')
            ->assertSee('192.168.1.1')
            ->assertSee('HTTP OK')
            ->assertSeeInOrder(['Last Changed', 'Last Checked', 'HTTP Web Server Check', '3 days', '5 minutes']);
    }

    public function testAuthorizedUserCanRenderServicesDetailsView(): void
    {
        $device = Device::factory()->create();
        Service::factory()->for($device)->create([
            'service_type' => 'ping',
            'service_desc' => 'Ping Check',
            'service_ip' => '192.168.1.1',
            'service_status' => 0,
            'service_changed' => time() - 3 * 86400,
            'service_checked' => 0,
            'service_ds' => json_encode(['rta' => ['full_name' => 'Round Trip Average']]),
            'service_ignore' => 0,
            'service_disabled' => 0,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'services', 'view' => 'details']))
            ->assertOk()
            ->assertSee('Ping Check')
            ->assertSee('Round Trip Average')
            ->assertSeeInOrder(['Last Checked', 'Ping Check', '3 days', 'Not yet recorded'])
            ->assertSee('colspan="8"', false);
    }

    public function testGlobalServicesPageShowsRecordedAndUnknownCheckTimes(): void
    {
        $device = Device::factory()->create();
        Service::factory()->for($device)->create([
            'service_type' => 'http',
            'service_name' => 'Recorded Check',
            'service_changed' => time() - 3 * 86400,
            'service_checked' => time() - 300,
        ]);
        Service::factory()->for($device)->create([
            'service_type' => 'http',
            'service_name' => 'Unrecorded Check',
            'service_changed' => time() - 3 * 86400,
            'service_checked' => 0,
        ]);
        Service::factory()->for($device)->create([
            'service_type' => 'http',
            'service_name' => 'Waiting Check',
            'service_changed' => 0,
            'service_checked' => 0,
        ]);

        // Legacy pages parse their path from the server globals.
        $previousUri = $_SERVER['REQUEST_URI'] ?? null;
        $_SERVER['REQUEST_URI'] = '/services';

        try {
            $this->actingAs($this->admin())
                ->get('/services')
                ->assertOk()
                ->assertSeeInOrder([
                    'Last Changed',
                    'Last Checked',
                    'Recorded Check',
                    '3 days',
                    '5 minutes',
                    'Unrecorded Check',
                    '3 days',
                    'Not yet recorded',
                    'Waiting Check',
                    'Waiting for first check',
                    'Not yet recorded',
                ])
                ->assertDontSee('years');
        } finally {
            if ($previousUri === null) {
                unset($_SERVER['REQUEST_URI']);
            } else {
                $_SERVER['REQUEST_URI'] = $previousUri;
            }
        }
    }

    public function testServicesViewValidationFailsOnInvalidView(): void
    {
        $device = Device::factory()->create();
        Service::factory()->for($device)->create([
            'service_type' => 'http',
            'service_ignore' => 0,
            'service_disabled' => 0,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'services', 'view' => 'invalid']))
            ->assertInvalid(['view']);
    }

    public function testUserWithoutServicePermissionGetsForbidden(): void
    {
        $device = Device::factory()->create();
        Service::factory()->for($device)->create([
            'service_type' => 'http',
            'service_ignore' => 0,
            'service_disabled' => 0,
        ]);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('device', ['device' => $device, 'tab' => 'services']))
            ->assertForbidden();
    }
}
