<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class LocationsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    public function testPageRendersTableWithDataUrl(): void
    {
        $this->actingAs($this->admin())
            ->get('/locations')
            ->assertOk()
            ->assertSee('id="locations"', false)
            ->assertSee('data-url="' . route('table.location') . '"', false);
    }

    public function testTableOnlyReturnsLocationsOfAccessibleDevices(): void
    {
        $visible = Location::factory()->create(['location' => 'Visible']);
        $hidden = Location::factory()->create(['location' => 'Hidden']);
        $device = Device::factory()->create(['location_id' => $visible->id]);
        Device::factory()->create(['location_id' => $hidden->id]);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->devicesOwned()->attach($device);

        $response = $this->actingAs($user)->postJson(route('table.location'), $this->tableRequest());

        $response->assertOk()->assertJsonPath('total', 1);
        $this->assertStringNotContainsString('Hidden', $response->getContent());
    }

    public function testTableEscapesLocationNames(): void
    {
        $name = '"><script>alert(1)</script>';
        Location::factory()->create(['location' => $name]);

        $response = $this->actingAs($this->admin())
            ->postJson(route('table.location'), $this->tableRequest())
            ->assertOk()
            ->assertJsonPath('rows.0.location', e($name));

        $this->assertStringNotContainsString('<script>', $response->getContent());
    }

    public function testExportKeepsRawLocationNames(): void
    {
        Location::factory()->create(['location' => 'R&D "Lab"']);

        $csv = $this->actingAs($this->admin())->get('/ajax/table/location/export')->streamedContent();

        $this->assertStringContainsString('R&D ""Lab""', $csv);
        $this->assertStringNotContainsString('&amp;', $csv);
    }

    public function testExportReturnsCsvWithHeadersAndRows(): void
    {
        $location = Location::factory()->create(['location' => 'Berlin', 'lat' => 52.5, 'lng' => 13.4]);
        Device::factory()->create(['location_id' => $location->id]);

        $response = $this->actingAs($this->admin())->get('/ajax/table/location/export');

        $response->assertOk();
        $csv = preg_replace('/^\xEF\xBB\xBF/', '', $response->streamedContent());
        $lines = array_values(array_filter(explode("\n", trim($csv))));

        $this->assertCount(2, $lines);
        $this->assertSame(['ID', 'Location', 'Latitude', 'Longitude', 'Devices', 'Down'], str_getcsv($lines[0]));
        $this->assertSame('Berlin', str_getcsv($lines[1])[1]);
        $this->assertSame('1', str_getcsv($lines[1])[4]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    /** @return array<string, mixed> */
    private function tableRequest(): array
    {
        return [
            'current' => 1,
            'rowCount' => 50,
            'searchPhrase' => '',
        ];
    }
}
