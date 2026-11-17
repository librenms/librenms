<?php

/**
 * AjaxTableAuthorizationTest.php
 *
 * Verifies that legacy ajax tables only return data for devices the user can access.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 */

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Models\Component;
use App\Models\ComponentPref;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Role;

final class AjaxTableAuthorizationTest extends DBTestCase
{
    use DatabaseTransactions;

    private Device $accessible;
    private Device $other;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('user');
        $this->accessible = Device::factory()->create(['bgpLocalAs' => 64496]);
        $this->other = Device::factory()->create(['bgpLocalAs' => 64497]);
        LibrenmsConfig::set('astext.64496', 'AS-ACCESSIBLE');
        LibrenmsConfig::set('astext.64497', 'AS-OTHER');
    }

    private function limitedUser(): User
    {
        /** @var User $user */
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->devicesOwned()->attach($this->accessible->device_id);

        return $user;
    }

    private function adminUser(): User
    {
        /** @var User $user */
        $user = User::factory()->admin()->create(['enabled' => 1]);

        return $user;
    }

    private function ntpComponent(Device $device, string $peer): Component
    {
        /** @var Component $component */
        $component = Component::factory()->create(['device_id' => $device->device_id, 'type' => 'ntp', 'label' => $peer]);
        foreach (['peer' => $peer, 'stratum' => '2', 'error' => ''] as $attribute => $value) {
            ComponentPref::create(['component' => $component->id, 'attribute' => $attribute, 'value' => $value]);
        }

        return $component;
    }

    /**
     * Run a legacy ajax table include the way html/ajax_table.php does and return the decoded output.
     *
     * @param  array<string, mixed>  $vars
     * @return array{current: int, rowCount: int, rows: list<array<string, mixed>>, total: int}
     */
    private function runTable(string $id, array $vars = []): array
    {
        $current = 1;
        $rowCount = 50;
        $sort = '';
        $searchPhrase = '';
        $response = [];
        $vars += ['current' => $current, 'rowCount' => $rowCount, 'searchPhrase' => $searchPhrase];

        ob_start();
        try {
            include base_path("includes/html/table/$id.inc.php");
        } finally {
            $output = (string) ob_get_clean();
        }

        return json_decode($output, true, flags: JSON_THROW_ON_ERROR);
    }

    public function testNtpTableOnlyListsAccessibleDevices(): void
    {
        $this->ntpComponent($this->accessible, '192.0.2.1');
        $this->ntpComponent($this->other, '192.0.2.2');
        $vars = ['view' => 'all', 'graph' => 'none'];

        $this->actingAs($this->limitedUser());
        $limited = $this->runTable('app_ntp', $vars);
        $this->assertSame(['192.0.2.1'], array_column($limited['rows'], 'peer'));
        $this->assertSame(1, $limited['total']);

        $this->actingAs($this->adminUser());
        $all = $this->runTable('app_ntp', $vars);
        $this->assertEqualsCanonicalizing(['192.0.2.1', '192.0.2.2'], array_column($all['rows'], 'peer'));
    }
}
