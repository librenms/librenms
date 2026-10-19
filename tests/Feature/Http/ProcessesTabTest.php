<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Process;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class ProcessesTabTest extends TestCase
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

    public function testAuthorizedUserCanRenderProcessesTab(): void
    {
        $device = Device::factory()->create();
        Process::factory()->for($device)->create([
            'pid' => 1234,
            'vsz' => 2048,
            'rss' => 1024,
            'cputime' => '00:05:12',
            'user' => 'root',
            'command' => '/usr/sbin/snmpd -Lsd -Lf /dev/null',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'processes']))
            ->assertOk()
            ->assertSee('1234')
            ->assertSee('root')
            ->assertSee('00:05:12')
            ->assertSee('/usr/sbin/snmpd -Lsd -Lf /dev/null')
            ->assertSee(\LibreNMS\Util\Number::formatSi(2048 * 1024, 2, 0, ''));
    }

    public function testAuthorizedUserCanSortProcesses(): void
    {
        $device = Device::factory()->create();
        Process::factory()->for($device)->create([
            'pid' => 10,
            'user' => 'zebra',
        ]);
        Process::factory()->for($device)->create([
            'pid' => 20,
            'user' => 'alpha',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'processes', 'order' => 'user', 'by' => 'asc']))
            ->assertOk();

        $content = $response->getContent();
        $alphaPos = strpos($content, 'alpha');
        $zebraPos = strpos($content, 'zebra');

        $this->assertTrue($alphaPos !== false && $zebraPos !== false && $alphaPos < $zebraPos);
    }

    public function testInvalidSortParametersFailValidation(): void
    {
        $device = Device::factory()->create();
        Process::factory()->for($device)->create();

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'processes', 'order' => 'invalid_col', 'by' => 'invalid_dir']))
            ->assertSessionHasErrors(['order', 'by']);
    }

    public function testUserWithoutAccessGetsForbidden(): void
    {
        $device = Device::factory()->create();
        Process::factory()->for($device)->create();

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('device', ['device' => $device, 'tab' => 'processes']))
            ->assertForbidden();
    }
}
