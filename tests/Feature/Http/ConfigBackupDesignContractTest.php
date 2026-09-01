<?php

/**
 * ConfigBackupDesignContractTest.php
 *
 * Contract and invariant test suite enforcing the specifications in
 * doc/Developing/Config-Backups-UI-UX.md without coupling to internal
 * client-side implementation details.
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

namespace LibreNMS\Tests\Feature\Http;

use App\ConfigBackup\ConfigBackupManager;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class ConfigBackupDesignContractTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var list<class-string<\LibreNMS\Interfaces\ConfigBackupProvider>>
     */
    private array $originalProviders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalProviders = ConfigBackupManager::$providers;
        ConfigBackupManager::$providers = [
            \App\ConfigBackup\Providers\UnimusProvider::class,
            \App\ConfigBackup\Providers\OxidizedProvider::class,
        ];

        Role::findOrCreate('admin');
        Role::findOrCreate('user');

        LibrenmsConfig::set('unimus.enabled', true);
        LibrenmsConfig::set('unimus.url', 'http://unimus:8085');
        LibrenmsConfig::set('unimus.api_version', 'v2');
        LibrenmsConfig::set('unimus.token', 'test-token');
    }

    protected function tearDown(): void
    {
        ConfigBackupManager::$providers = $this->originalProviders;
        parent::tearDown();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    /**
     * Invariant 1 (Chronological Ordering):
     * Backups must be listed in reverse chronological order (newest first).
     */
    public function testInvariant1ChronologicalOrdering(): void
    {
        $device = Device::factory()->create();

        Http::fake([
            'unimus:8085/api/v2/devices/findByAddress/*' => Http::response(['data' => ['id' => 7]], 200),
            'unimus:8085/api/v2/devices/7/backups*' => Http::response([
                'data' => [
                    ['id' => 20, 'validSince' => 500, 'validUntil' => null, 'type' => 'TEXT'],
                    ['id' => 19, 'validSince' => 400, 'validUntil' => 500, 'type' => 'TEXT'],
                    ['id' => 18, 'validSince' => 300, 'validUntil' => 400, 'type' => 'TEXT'],
                ],
                'paginator' => ['totalCount' => 3, 'totalPages' => 1, 'page' => 0, 'size' => 50],
            ], 200),
        ]);

        $response = $this->actingAs($this->admin())
            ->getJson(route('device.config.backups', ['device' => $device->device_id, 'page' => 0]))
            ->assertOk();

        $backups = $response->json('backups');
        $this->assertCount(3, $backups);
        $this->assertSame('20', $backups[0]['id']);
        $this->assertSame('19', $backups[1]['id']);
        $this->assertSame('18', $backups[2]['id']);
        $this->assertGreaterThan($backups[1]['date'], $backups[0]['date']);
        $this->assertGreaterThan($backups[2]['date'], $backups[1]['date']);
    }

    /**
     * Invariant 5 (No Self-Diff):
     * A revision cannot be diffed against itself. The API must reject orig == rev.
     */
    public function testInvariant5DiffRejectsIdenticalRevisions(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->getJson(route('device.config.diff', ['device' => $device->device_id, 'orig' => 10, 'rev' => 10]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rev']);
    }

    /**
     * Invariant 9 (Lazy Payload Loading):
     * The backup listing must omit full text content to preserve performance.
     */
    public function testInvariant9BackupListOmitsFullContent(): void
    {
        $device = Device::factory()->create();

        Http::fake([
            'unimus:8085/api/v2/devices/findByAddress/*' => Http::response(['data' => ['id' => 7]], 200),
            'unimus:8085/api/v2/devices/7/backups*' => Http::response([
                'data' => [
                    ['id' => 1, 'validSince' => 100, 'validUntil' => null, 'type' => 'TEXT', 'bytes' => base64_encode('large config')],
                ],
                'paginator' => ['totalCount' => 1, 'totalPages' => 1, 'page' => 0, 'size' => 50],
            ], 200),
        ]);

        $this->actingAs($this->admin())
            ->getJson(route('device.config.backups', ['device' => $device->device_id, 'page' => 0]))
            ->assertOk()
            ->assertJsonPath('backups.0.content', null);
    }

    /**
     * Invariant 14 (Vendor-Neutral Diff Normalization):
     * Diff outputs must be normalized to standard change group structures with line numbers.
     */
    public function testInvariant14DiffNormalizesToStandardGroups(): void
    {
        $device = Device::factory()->create();

        Http::fake([
            'unimus:8085/api/v2/backups/diff*' => Http::response([
                'data' => [
                    'origDeviceInfo' => ['id' => 0, 'address' => '10.0.0.1', 'type' => 'IOS'],
                    'revDeviceInfo' => ['id' => 0, 'address' => '10.0.0.1', 'type' => 'IOS'],
                    'lineGroups' => [
                        [
                            'type' => 'COMMON',
                            'originalLines' => [['number' => 1, 'text' => 'hostname router']],
                            'revisedLines' => [['number' => 1, 'text' => 'hostname router']],
                        ],
                        [
                            'type' => 'INSERTED',
                            'originalLines' => [],
                            'revisedLines' => [['number' => 2, 'text' => 'interface GigabitEthernet0/1']],
                        ],
                        [
                            'type' => 'DELETED',
                            'originalLines' => [['number' => 2, 'text' => 'interface FastEthernet0/1']],
                            'revisedLines' => [],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->admin())
            ->getJson(route('device.config.diff', ['device' => $device->device_id, 'orig' => 1, 'rev' => 2]))
            ->assertOk();

        $groups = $response->json('groups');
        $this->assertCount(3, $groups);
        $this->assertSame('COMMON', $groups[0]['type']);
        $this->assertSame('INSERTED', $groups[1]['type']);
        $this->assertSame('DELETED', $groups[2]['type']);
        $this->assertSame(1, $groups[0]['original'][0]['line']);
        $this->assertSame('hostname router', $groups[0]['original'][0]['text']);
        $this->assertSame(2, $groups[1]['revised'][0]['line']);
        $this->assertSame('interface GigabitEthernet0/1', $groups[1]['revised'][0]['text']);
    }

    /**
     * Invariant 13 (Identical Configurations Diff State):
     * When diffing revisions with no differences, the API returns COMMON-only groups.
     */
    public function testInvariant13IdenticalDiffContract(): void
    {
        $device = Device::factory()->create();

        Http::fake([
            'unimus:8085/api/v2/backups/diff*' => Http::response([
                'data' => [
                    'origDeviceInfo' => ['id' => 0, 'address' => '10.0.0.1', 'type' => 'IOS'],
                    'revDeviceInfo' => ['id' => 0, 'address' => '10.0.0.1', 'type' => 'IOS'],
                    'lineGroups' => [
                        [
                            'type' => 'COMMON',
                            'originalLines' => [['number' => 1, 'text' => 'hostname switch']],
                            'revisedLines' => [['number' => 1, 'text' => 'hostname switch']],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->admin())
            ->getJson(route('device.config.diff', ['device' => $device->device_id, 'orig' => 1, 'rev' => 2]))
            ->assertOk();

        $groups = $response->json('groups');
        $this->assertCount(1, $groups);
        $this->assertSame('COMMON', $groups[0]['type']);
        $this->assertEmpty(array_filter($groups, fn ($g) => in_array($g['type'], ['INSERTED', 'DELETED', 'CHANGED'])));
    }

    /**
     * Invariant 4 (Binary Configurations Flagged):
     * Backups marked as BINARY are accurately identified in the payload.
     */
    public function testInvariant4BinaryBackupsFlagged(): void
    {
        $device = Device::factory()->create();

        Http::fake([
            'unimus:8085/api/v2/devices/findByAddress/*' => Http::response(['data' => ['id' => 7]], 200),
            'unimus:8085/api/v2/devices/7/backups*' => Http::response([
                'data' => [
                    ['id' => 5, 'validSince' => 100, 'validUntil' => null, 'type' => 'BINARY'],
                ],
                'paginator' => ['totalCount' => 1, 'totalPages' => 1, 'page' => 0, 'size' => 50],
            ], 200),
        ]);

        $this->actingAs($this->admin())
            ->getJson(route('device.config.backups', ['device' => $device->device_id, 'page' => 0]))
            ->assertOk()
            ->assertJsonPath('backups.0.type', 'BINARY');
    }

    /**
     * Invariant 10 (History Boundaries & Pagination Contract):
     * Out-of-bounds page requests or negative pages are rejected via validation.
     */
    public function testInvariant10HistoryBoundaries(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->getJson(route('device.config.backups', ['device' => $device->device_id, 'page' => -1]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['page']);
    }

    /**
     * UI Design Contract (Section 4 & 8: Keyboard Shortcuts & Help Modal):
     * The Blade view contract must render all documented interaction bindings and help sections.
     */
    public function testViewEnshrinesKeyboardShortcutsAndInteractionContract(): void
    {
        $device = Device::factory()->create();

        Http::fake([
            'unimus:8085/api/v2/devices/findByAddress/*' => Http::response(['data' => ['id' => 7]], 200),
        ]);

        $view = $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'config']))
            ->assertOk();

        // Alpine & Viewer Components
        $view->assertSee('data-config-backups', false);
        $view->assertSee('x-config-highlight="selected.content"', false);
        $view->assertSee('class="config-highlight line-numbers', false);

        // Diff Table Header Contract (Concept B)
        $view->assertSee('diffRangeSummaryText', false);

        // Keyboard Shortcut Localization Strings & Modifiers
        $view->assertSee('x-text="modifierKey"', false);
        $view->assertSee(__('config_backups.navigate_history'));
        $view->assertSee(__('config_backups.compare_revisions'));
        $view->assertSee(__('config_backups.shortcut_older'));
        $view->assertSee(__('config_backups.shortcut_newer'));
        $view->assertSee(__('config_backups.shortcut_toggle_diff'));
        $view->assertSee(__('config_backups.shortcut_shift_click'));
        $view->assertSee(__('config_backups.shortcut_reverse_diff'));
        $view->assertSee(__('config_backups.shortcut_expand_older'));
        $view->assertSee(__('config_backups.shortcut_expand_newer'));
    }
}
