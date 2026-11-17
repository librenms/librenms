<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Syslog;
use App\Models\User;
use LibreNMS\Tests\InMemoryDbTestCase;
use Spatie\Permission\Models\Permission;

final class SyslogExpungeTest extends InMemoryDbTestCase
{
    public function testClearingSyslogRequiresPermissionAndPost(): void
    {
        $delete = Permission::findOrCreate('syslog.delete');
        $view = Permission::findOrCreate('syslog.viewAll');
        $user = User::factory()->create(['enabled' => 1])->givePermissionTo($view);
        $devices = Device::factory()->count(2)->create();
        foreach ($devices as $device) {
            Syslog::factory()->for($device)->create(['facility' => 'local0']);
        }

        $this->actingAs($user)->get(route('syslog'))->assertOk()->assertDontSee('Clear Syslog');
        $this->get('/syslog/action=expunge')->assertForbidden();
        $this->post(route('syslog.expunge'))->assertForbidden();
        $this->assertDatabaseCount('syslog', 2);

        $user->givePermissionTo($delete);
        $this->get(route('syslog'))->assertOk()->assertDontSee('Clear Syslog');
        $this->get('/syslog/action=expunge')->assertOk()->assertSee(route('syslog.expunge'));
        $this->assertDatabaseCount('syslog', 2);

        $this->post(route('syslog.expunge'))->assertRedirect(route('syslog'));
        $this->assertDatabaseCount('syslog', 0);
        $this->get(route('syslog'))->assertOk()->assertSee('Syslog cleared.');
    }
}
