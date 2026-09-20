<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Bill;
use App\Models\BillData;
use App\Models\BillHistory;
use App\Models\BillPerm;
use App\Models\BillPort;
use App\Models\BillPortCounter;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BillControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup basic roles
        Role::findOrCreate('admin');
        Role::findOrCreate('user');

        // Setup permissions
        Permission::findOrCreate('bill.viewAny');
        Permission::findOrCreate('bill.view');
        Permission::findOrCreate('bill.create');
        Permission::findOrCreate('bill.update');
        Permission::findOrCreate('bill.delete');

        LibrenmsConfig::set('enable_billing', 1);
        LibrenmsConfig::set('billing.base', 1000);
    }

    public function testAdminCanUpdateBillQuota(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $bill = Bill::factory()->create([
            'bill_name' => 'Original Name',
            'bill_type' => 'quota',
            'bill_day' => 5,
        ]);

        $response = $this->actingAs($admin)->put(route('bill.update', $bill), [
            'bill_name' => 'Updated Quota Bill',
            'bill_type' => 'quota',
            'bill_day' => 10,
            'bill_quota' => 500,
            'bill_quota_type' => 'MB',
            'bill_custid' => 'CUST-123',
            'bill_ref' => 'REF-456',
            'bill_notes' => 'Notes here',
        ]);

        $response->assertRedirect();
        $bill->refresh();
        $this->assertEquals('Updated Quota Bill', $bill->bill_name);
        $this->assertEquals(10, $bill->bill_day);
        $this->assertEquals('quota', $bill->bill_type);
        // 500 MB with base 1000 -> 500 * 1000 * 1000 = 500000000
        $this->assertEquals(500000000, $bill->bill_quota);
        $this->assertEquals(0, $bill->bill_cdr);
        $this->assertEquals('CUST-123', $bill->bill_custid);
        $this->assertEquals('REF-456', $bill->bill_ref);
        $this->assertEquals('Notes here', $bill->bill_notes);
    }

    public function testAdminCanUpdateBillCdr(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $bill = Bill::factory()->create([
            'bill_name' => 'Original CDR Bill',
            'bill_type' => 'cdr',
            'bill_day' => 1,
        ]);

        $response = $this->actingAs($admin)->put(route('bill.update', $bill), [
            'bill_name' => 'Updated CDR Bill',
            'bill_type' => 'cdr',
            'bill_day' => 15,
            'bill_cdr' => 100,
            'bill_cdr_type' => 'Mbps',
            'dir_95th' => 'agg',
        ]);

        $response->assertRedirect();
        $bill->refresh();
        $this->assertEquals('Updated CDR Bill', $bill->bill_name);
        $this->assertEquals(15, $bill->bill_day);
        $this->assertEquals('cdr', $bill->bill_type);
        // 100 Mbps with base 1000 -> 100 * 1000 * 1000 = 100000000
        $this->assertEquals(100000000, $bill->bill_cdr);
        $this->assertEquals(0, $bill->bill_quota);
        $this->assertEquals('agg', $bill->dir_95th);
    }

    public function testUnauthorizedUserCannotUpdateBill(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $bill = Bill::factory()->create([
            'bill_name' => 'Protected Bill',
        ]);

        $response = $this->actingAs($user)->put(route('bill.update', $bill), [
            'bill_name' => 'Hacked Bill',
            'bill_type' => 'quota',
            'bill_day' => 1,
        ]);

        $response->assertForbidden();
        $this->assertEquals('Protected Bill', $bill->fresh()->bill_name);
    }

    public function testAuthorizedUserWithBillAccessCanUpdateBill(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->givePermissionTo('bill.update');

        $bill = Bill::factory()->create([
            'bill_name' => 'Assigned Bill',
            'bill_type' => 'quota',
            'bill_day' => 1,
        ]);

        // Assign permission for this specific bill
        BillPerm::query()->insert([
            'user_id' => $user->user_id,
            'bill_id' => $bill->bill_id,
        ]);

        $response = $this->actingAs($user)->put(route('bill.update', $bill), [
            'bill_name' => 'User Updated Bill',
            'bill_type' => 'quota',
            'bill_day' => 20,
        ]);

        $response->assertRedirect();
        $this->assertEquals('User Updated Bill', $bill->fresh()->bill_name);
    }

    public function testAdminCanDeleteBillWithCascadingRelations(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);

        $bill = Bill::factory()->create();

        // Create related records
        BillData::create([
            'bill_id' => $bill->bill_id,
            'period' => 300,
            'delta' => 1000,
            'in_delta' => 500,
            'out_delta' => 500,
        ]);

        BillHistory::create([
            'bill_id' => $bill->bill_id,
            'bill_datefrom' => now()->subMonth(),
            'bill_dateto' => now(),
            'bill_type' => 'quota',
            'bill_allowed' => 10000,
            'bill_used' => 5000,
            'bill_overuse' => 0,
            'bill_percent' => 50,
            'rate_95th_in' => 0,
            'rate_95th_out' => 0,
            'rate_95th' => 0,
            'dir_95th' => 'in',
            'rate_average' => 0,
            'rate_average_in' => 0,
            'rate_average_out' => 0,
            'traf_in' => 0,
            'traf_out' => 0,
            'traf_total' => 0,
            'bill_peak_out' => 0,
            'bill_peak_in' => 0,
        ]);

        BillPort::query()->insert([
            'bill_id' => $bill->bill_id,
            'port_id' => $port->port_id,
        ]);

        BillPerm::query()->insert([
            'bill_id' => $bill->bill_id,
            'user_id' => $admin->user_id,
        ]);

        BillPortCounter::create([
            'bill_id' => $bill->bill_id,
            'port_id' => $port->port_id,
            'timestamp' => now(),
            'in_counter' => 100,
            'in_delta' => 10,
            'out_counter' => 100,
            'out_delta' => 10,
        ]);

        $response = $this->actingAs($admin)->delete(route('bill.destroy', $bill));

        $response->assertRedirect(url('bills'));
        $this->assertDatabaseMissing('bills', ['bill_id' => $bill->bill_id]);
        $this->assertDatabaseMissing('bill_data', ['bill_id' => $bill->bill_id]);
        $this->assertDatabaseMissing('bill_history', ['bill_id' => $bill->bill_id]);
        $this->assertDatabaseMissing('bill_ports', ['bill_id' => $bill->bill_id]);
        $this->assertDatabaseMissing('bill_perms', ['bill_id' => $bill->bill_id]);
        $this->assertDatabaseMissing('bill_port_counters', ['bill_id' => $bill->bill_id]);
    }

    public function testUnauthorizedUserCannotDeleteBill(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $bill = Bill::factory()->create();

        $response = $this->actingAs($user)->delete(route('bill.destroy', $bill));

        $response->assertForbidden();
        $this->assertDatabaseHas('bills', ['bill_id' => $bill->bill_id]);
    }

    public function testAdminCanResetBill(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $bill = Bill::factory()->create();

        BillData::create([
            'bill_id' => $bill->bill_id,
            'period' => 300,
            'delta' => 1000,
            'in_delta' => 500,
            'out_delta' => 500,
        ]);

        BillHistory::create([
            'bill_id' => $bill->bill_id,
            'bill_datefrom' => now()->subMonth(),
            'bill_dateto' => now(),
            'bill_type' => 'quota',
            'bill_allowed' => 10000,
            'bill_used' => 5000,
            'bill_overuse' => 0,
            'bill_percent' => 50,
            'rate_95th_in' => 0,
            'rate_95th_out' => 0,
            'rate_95th' => 0,
            'dir_95th' => 'in',
            'rate_average' => 0,
            'rate_average_in' => 0,
            'rate_average_out' => 0,
            'traf_in' => 0,
            'traf_out' => 0,
            'traf_total' => 0,
            'bill_peak_out' => 0,
            'bill_peak_in' => 0,
        ]);

        $response = $this->actingAs($admin)->post(route('bill.reset', $bill), [
            'confirm' => 'mysql',
        ]);

        $response->assertRedirect(url('bills'));
        $this->assertDatabaseHas('bills', ['bill_id' => $bill->bill_id]);
        $this->assertDatabaseMissing('bill_data', ['bill_id' => $bill->bill_id]);
        $this->assertDatabaseMissing('bill_history', ['bill_id' => $bill->bill_id]);
    }

    public function testAdminCanAttachAndDetachPort(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $bill = Bill::factory()->create();

        // Attach
        $attachResponse = $this->actingAs($admin)->post(route('bill.port.attach', $bill), [
            'port_id' => $port->port_id,
        ]);

        $attachResponse->assertRedirect();
        $this->assertDatabaseHas('bill_ports', [
            'bill_id' => $bill->bill_id,
            'port_id' => $port->port_id,
        ]);

        // Detach
        $detachResponse = $this->actingAs($admin)->delete(route('bill.port.detach', [$bill, $port]));

        $detachResponse->assertRedirect();
        $this->assertDatabaseMissing('bill_ports', [
            'bill_id' => $bill->bill_id,
            'port_id' => $port->port_id,
        ]);
    }
}
