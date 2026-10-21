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

    public function testCustomerGraphLinksUseCustomerName(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create(['enabled' => 1])->assignRole('admin');

        $device = Device::factory()->create();
        Port::factory()->for($device)->create([
            'port_descr_type' => 'cust',
            'port_descr_descr' => 'Example Customer',
        ]);

        $response = $this->actingAs($admin)->postJson('/ajax/table/customers', [
            'current' => 1,
            'rowCount' => 50,
            'searchPhrase' => '',
        ]);

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('customer=Example+Customer', $content);
        $this->assertStringNotContainsString('customer_bits/id=', $content);
    }
}
