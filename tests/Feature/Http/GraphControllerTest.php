<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Bill;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class GraphControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        LibrenmsConfig::set('auth_mechanism', 'mysql');
        LibrenmsConfig::set('webui.graph_type', 'png');
    }

    public function testRendersImage(): void
    {
        $bill = Bill::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graph?type=bill_historicmonthly&id=$bill->bill_id&width=600&height=250");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertStringStartsWith("\x89PNG", $response->getContent());
    }

    public function testUserTitleMayContainPunctuation(): void
    {
        $bill = Bill::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graph?type=bill_historicmonthly&id=$bill->bill_id&graph_title=" . urlencode("Bob's (bill)"));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function testUnknownGraphRespondsLikeUnauthorized(): void
    {
        $device = Device::factory()->create();

        $unknown = $this->actingAs($this->adminUser())->get("/graph?type=device_doesnotexist&device=$device->device_id");
        $unauthorized = $this->actingAs(User::factory()->create(['enabled' => 1]))->get("/graph?type=device_processor&device=$device->device_id");

        foreach ([$unknown, $unauthorized] as $response) {
            $response->assertForbidden();
            $response->assertHeader('Content-Type', 'image/png');
        }
        $this->assertSame($unknown->getContent(), $unauthorized->getContent());
    }

    public function testInvalidInputRespondsWithErrorImage(): void
    {
        $device = Device::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graph?type=device_processor&device=$device->device_id&width=abc");

        $response->assertUnprocessable();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function testGraphErrorRespondsWithErrorImage(): void
    {
        $device = Device::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graph?type=device_processor&device=$device->device_id");

        $response->assertServerError();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function testGraphSpecificValidationRespondsWithErrorImage(): void
    {
        $bill = Bill::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graph?type=bill_historictransfer&id=$bill->bill_id&imgtype=week");

        $response->assertUnprocessable();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function testArrayInputDoesNotBreakAuthorization(): void
    {
        $response = $this->actingAs($this->adminUser())->get('/graph?type=bill_historicmonthly&id[]=1');

        $response->assertForbidden();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function testLegacyAuthErrorsRespondLikeUnauthorized(): void
    {
        $response = $this->actingAs($this->adminUser())->get('/graph?type=bill_bits');

        $response->assertForbidden();
        $response->assertHeader('Content-Type', 'image/png');
    }

    public function testGraphSizeIsLimited(): void
    {
        $bill = Bill::factory()->create();

        $response = $this->actingAs($this->adminUser())
            ->get("/graph?type=bill_historicmonthly&id=$bill->bill_id&width=200000&height=200000");

        $response->assertUnprocessable();
        $response->assertHeader('Content-Type', 'image/png');
    }

    private function adminUser(): User
    {
        return User::factory()->create(['enabled' => 1])->assignRole('admin');
    }
}
