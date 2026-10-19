<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Bill;
use App\Models\BillHistory;
use App\Models\BillPerm;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BillPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');

        foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
            Permission::findOrCreate("bill.$ability");
        }

        LibrenmsConfig::set('enable_billing', 1);
        LibrenmsConfig::set('billing.base', 1000);
    }

    public function testIndexPageRenders(): void
    {
        $this->actingAs($this->admin())
            ->get(route('bills.index'))
            ->assertOk()
            ->assertSee(route('table.bills'), false)
            ->assertSee(__('Add Traffic Bill'));
    }

    public function testIndexPrefillsCreateFormFromPort(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->create([
            'device_id' => $device->device_id,
            'port_descr_descr' => 'Customer Uplink',
            'port_descr_circuit' => 'CIRCUIT-42',
        ]);

        $this->actingAs($this->admin())
            ->get(route('bills.index', ['port' => $port->port_id]))
            ->assertOk()
            ->assertSee('value="Customer Uplink"', false)
            ->assertSee('value="CIRCUIT-42"', false)
            ->assertSee('createBill: true', false);
    }

    public function testIndexRequiresBillingEnabled(): void
    {
        LibrenmsConfig::set('enable_billing', 0);

        $this->actingAs($this->user())
            ->get(route('bills.index'))
            ->assertForbidden();
    }

    public function testAdminCanCreateCdrBillWithPort(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);

        $response = $this->actingAs($this->admin())->post(route('bill.store'), [
            'bill_name' => 'New CDR Bill',
            'bill_type' => 'cdr',
            'bill_day' => 3,
            'bill_cdr' => 100,
            'bill_cdr_type' => 'Mbps',
            'bill_quota' => 5,
            'bill_quota_type' => 'GB',
            'dir_95th' => 'agg',
            'port_id' => $port->port_id,
        ]);

        $bill = Bill::where('bill_name', 'New CDR Bill')->firstOrFail();
        $response->assertRedirect(route('bill.edit', $bill));
        $this->assertEquals('cdr', $bill->bill_type);
        $this->assertEquals(3, $bill->bill_day);
        $this->assertEquals(100000000, $bill->bill_cdr);
        $this->assertEquals(0, $bill->bill_quota);
        $this->assertEquals('agg', $bill->dir_95th);
        $this->assertDatabaseHas('bill_ports', ['bill_id' => $bill->bill_id, 'port_id' => $port->port_id]);
    }

    public function testAdminCanCreateQuotaBillWithoutPort(): void
    {
        $this->actingAs($this->admin())->post(route('bill.store'), [
            'bill_name' => 'New Quota Bill',
            'bill_type' => 'quota',
            'bill_day' => 1,
            'bill_quota' => 2,
            'bill_quota_type' => 'TB',
        ])->assertRedirect();

        $bill = Bill::where('bill_name', 'New Quota Bill')->firstOrFail();
        $this->assertEquals(2000000000000, $bill->bill_quota);
        $this->assertEquals(0, $bill->bill_cdr);
        $this->assertEquals('in', $bill->dir_95th);
        $this->assertEquals(0, $bill->ports()->count());
    }

    public function testUnitsHonorBinaryBillingBase(): void
    {
        LibrenmsConfig::set('billing.base', 1024);

        $this->actingAs($this->admin())->post(route('bill.store'), [
            'bill_name' => 'Binary Bill',
            'bill_type' => 'quota',
            'bill_day' => 1,
            'bill_quota' => 1.5,
            'bill_quota_type' => 'GB',
        ])->assertRedirect();

        $bill = Bill::where('bill_name', 'Binary Bill')->firstOrFail();
        $this->assertEquals(1610612736, $bill->bill_quota);

        $this->actingAs($this->admin())
            ->get(route('bill.edit', $bill))
            ->assertSee('name="bill_quota" value="1.5"', false)
            ->assertSee('<option value="GB" selected', false);
    }

    public function testCreateBillValidatesInput(): void
    {
        $this->actingAs($this->admin())
            ->post(route('bill.store'), ['bill_type' => 'bogus', 'bill_day' => 40])
            ->assertSessionHasErrors(['bill_name', 'bill_type', 'bill_day']);

        $this->assertEquals(0, Bill::count());
    }

    public function testUserCannotCreateBill(): void
    {
        $this->actingAs($this->user())->post(route('bill.store'), [
            'bill_name' => 'Nope',
            'bill_type' => 'quota',
            'bill_day' => 1,
        ])->assertForbidden();

        $this->assertEquals(0, Bill::count());
    }

    public function testAdminCanViewAllBillPages(): void
    {
        $admin = $this->admin();
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id, 'ifName' => 'eth-billed', 'ifDescr' => 'eth-billed', 'ifAlias' => 'Billed Uplink']);
        $bill = Bill::factory()->create([
            'bill_name' => 'Viewable Bill',
            'bill_type' => 'quota',
            'bill_quota' => 5000000000,
            'total_data' => 6000000000,
            'total_data_in' => 4000000000,
            'total_data_out' => 2000000000,
        ]);
        $bill->ports()->attach($port->port_id);
        $history = $this->createHistory($bill, now()->subMonths(2), now()->subMonth());

        foreach (['show', 'accurate', 'transfer', 'history', 'edit'] as $page) {
            $response = $this->actingAs($admin)
                ->get(route("bill.$page", $bill))
                ->assertOk()
                ->assertSee('Viewable Bill');

            if ($page !== 'history') {
                $response->assertSee('eth-billed');
            }
        }

        $this->actingAs($admin)
            ->get(route('bill.transfer', $bill))
            ->assertSee(__('Overusage'));

        $this->actingAs($admin)
            ->get(route('bill.history', ['bill' => $bill, 'detail' => $history->bill_hist_id]))
            ->assertSee('bill_hist_id=' . $history->bill_hist_id, false);

        $this->actingAs($admin)
            ->get(route('bill.edit', $bill))
            ->assertSee('name="bill_quota" value="5"', false)
            ->assertSee('<option value="GB" selected', false);
    }

    public function testUserWithoutBillAccessCannotViewBill(): void
    {
        $bill = Bill::factory()->create();

        $this->actingAs($this->user())
            ->get(route('bill.show', $bill))
            ->assertForbidden();
    }

    public function testUserWithBillAccessCanViewButNotEdit(): void
    {
        $user = $this->user();
        $bill = Bill::factory()->create(['bill_name' => 'My Bill']);
        BillPerm::query()->insert(['user_id' => $user->user_id, 'bill_id' => $bill->bill_id]);

        $this->actingAs($user)
            ->get(route('bill.show', $bill))
            ->assertOk()
            ->assertSee('My Bill')
            ->assertDontSee(route('bill.edit', $bill))
            ->assertDontSee(__('Delete Bill'));

        $this->actingAs($user)
            ->get(route('bill.edit', $bill))
            ->assertForbidden();
    }

    public function testLegacyUrlsRedirect(): void
    {
        $admin = $this->admin();
        $bill = Bill::factory()->create();

        $this->actingAs($admin)->get("bill/bill_id={$bill->bill_id}")
            ->assertRedirect(route('bill.show', $bill));
        $this->actingAs($admin)->get("bill/bill_id={$bill->bill_id}/view=history")
            ->assertRedirect(route('bill.history', $bill));
        $this->actingAs($admin)->get("bill/bill_id={$bill->bill_id}/view=delete")
            ->assertRedirect(route('bill.edit', $bill));
    }

    public function testTableListsCurrentPeriodWithFilters(): void
    {
        $admin = $this->admin();
        $over = Bill::factory()->create(['bill_name' => 'Over Bill', 'bill_type' => 'cdr', 'bill_cdr' => 1000, 'rate_95th' => 2000]);
        Bill::factory()->create(['bill_name' => 'Under Bill', 'bill_type' => 'quota', 'bill_quota' => 1000, 'total_data' => 500]);

        $response = $this->actingAs($admin)->postJson(route('table.bills'), ['current' => 1, 'rowCount' => 50])
            ->assertOk()
            ->assertJsonPath('total', 2);
        $this->assertStringContainsString('Over Bill', $response->json('rows.0.bill_name'));
        $this->assertEquals('CDR', $response->json('rows.0.bill_type'));
        $this->assertNotEquals('-', $response->json('rows.0.overusage'));
        $this->assertStringContainsString(route('bill.edit', $over->bill_id), $response->json('rows.0.actions'));
        $this->assertEquals('-', $response->json('rows.1.overusage'));

        $response = $this->actingAs($admin)->postJson(route('table.bills'), ['state' => 'over'])
            ->assertJsonPath('total', 1);
        $this->assertStringContainsString('Over Bill', $response->json('rows.0.bill_name'));

        $response = $this->actingAs($admin)->postJson(route('table.bills'), ['bill_type' => 'quota'])
            ->assertJsonPath('total', 1);
        $this->assertStringContainsString('Under Bill', $response->json('rows.0.bill_name'));

        $this->actingAs($admin)->postJson(route('table.bills'), ['searchPhrase' => 'Under'])
            ->assertJsonPath('total', 1);

        $response = $this->actingAs($admin)->postJson(route('table.bills'), ['sort' => ['total_data' => 'desc']])
            ->assertOk();
        $this->assertStringContainsString('Under Bill', $response->json('rows.0.bill_name'));
    }

    public function testTableListsPreviousPeriod(): void
    {
        $admin = $this->admin();
        $bill = Bill::factory()->create(['bill_name' => 'Historic Bill', 'bill_type' => 'quota']);
        Bill::factory()->create(['bill_name' => 'No History Bill']);
        $this->createHistory($bill, now()->subMonth()->subDays(5), now()->subDays(5));

        $response = $this->actingAs($admin)->postJson(route('table.bills'), ['period' => 'prev', 'state' => 'over'])
            ->assertOk()
            ->assertJsonPath('total', 1);

        $row = $response->json('rows.0');
        $this->assertStringContainsString('Historic Bill', $row['bill_name']);
        $this->assertEquals('Quota', $row['bill_type']);
        $this->assertEquals('-', $row['predicted']);
        $this->assertEquals('', $row['actions']);
    }

    public function testTableOnlyShowsPermittedBills(): void
    {
        $user = $this->user();
        $mine = Bill::factory()->create(['bill_name' => 'Mine']);
        Bill::factory()->create(['bill_name' => 'Not Mine']);
        BillPerm::query()->insert(['user_id' => $user->user_id, 'bill_id' => $mine->bill_id]);

        $response = $this->actingAs($user)->postJson(route('table.bills'))
            ->assertOk()
            ->assertJsonPath('total', 1);
        $this->assertStringContainsString('Mine', $response->json('rows.0.bill_name'));
        $this->assertEquals('', $response->json('rows.0.actions'));
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    private function user(): User
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        return $user;
    }

    private function createHistory(Bill $bill, \DateTimeInterface $from, \DateTimeInterface $to): BillHistory
    {
        return BillHistory::create([
            'bill_id' => $bill->bill_id,
            'bill_datefrom' => $from,
            'bill_dateto' => $to,
            'bill_type' => 'Quota',
            'bill_allowed' => 10000,
            'bill_used' => 15000,
            'bill_overuse' => 5000,
            'bill_percent' => 150,
            'rate_95th_in' => 0,
            'rate_95th_out' => 0,
            'rate_95th' => 0,
            'dir_95th' => 'in',
            'rate_average' => 0,
            'rate_average_in' => 0,
            'rate_average_out' => 0,
            'traf_in' => 10000,
            'traf_out' => 5000,
            'traf_total' => 15000,
            'bill_peak_out' => 100,
            'bill_peak_in' => 200,
        ]);
    }
}
