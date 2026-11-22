<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class CustomersTableTest extends TestCase
{
    use RefreshDatabase;

    public function testPageRendersTable(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create(['enabled' => 1])->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('customers'))
            ->assertOk()
            ->assertSee('id="customers"', false)
            ->assertSee('data-url="' . route('table.customers') . '"', false);
    }

    public function testCustomerGraphLinksUseCustomerName(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create(['enabled' => 1])->assignRole('admin');

        $customer = 'Example + Customer / East';
        $device = Device::factory()->create();
        Port::factory()->for($device)->create([
            'port_descr_type' => 'cust',
            'port_descr_descr' => $customer,
        ]);

        $response = $this->actingAs($admin)->postJson('/ajax/table/customers', [
            'current' => 1,
            'rowCount' => 50,
            'searchPhrase' => '',
        ]);

        $response->assertOk();
        $graph = $response->json('rows.1.port_descr_descr');
        $this->assertSame(1, preg_match('/href="([^"]+)"/', $graph, $matches));
        $url = html_entity_decode($matches[1]);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertSame('/graphs', parse_url($url, PHP_URL_PATH));
        $this->assertSame('customer_bits', $query['type']);
        $this->assertSame($customer, $query['customer']);
    }
}
