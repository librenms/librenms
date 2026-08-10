<?php

/**
 * UnimusProviderTest.php
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

namespace LibreNMS\Tests\Unit;

use App\ApiClients\Unimus;
use App\ConfigBackup\Providers\UnimusProvider;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use LibreNMS\Interfaces\ConfigBackupProvider;
use LibreNMS\Tests\TestCase;

final class UnimusProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('unimus.enabled', true);
        LibrenmsConfig::set('unimus.url', 'http://unimus:8085');
        LibrenmsConfig::set('unimus.api_version', 'v2');
        LibrenmsConfig::set('unimus.token', 'test-token');
    }

    private function makeProvider(): UnimusProvider
    {
        return new UnimusProvider(new Unimus());
    }

    private function makeDevice(): Device
    {
        $device = new Device(['hostname' => 'router.example.com']);
        $device->device_id = 42;

        return $device;
    }

    public function testIsConfiguredDelegatesToClient(): void
    {
        $this->assertTrue(UnimusProvider::isConfigured());

        LibrenmsConfig::set('unimus.enabled', false);
        $this->assertFalse(UnimusProvider::isConfigured());
    }

    public function testSupportsAnyDevice(): void
    {
        $this->assertTrue($this->makeProvider()->supportsDevice($this->makeDevice()));
    }

    public function testBackupsStringifiesIds(): void
    {
        Http::fake([
            'unimus:8085/api/v2/devices/findByAddress/*' => Http::response(['data' => ['id' => 7]], 200),
            'unimus:8085/api/v2/devices/7/backups*' => Http::response([
                'data' => [
                    ['id' => 2, 'validSince' => 200, 'validUntil' => null, 'type' => 'TEXT', 'bytes' => base64_encode('config')],
                    ['id' => 0, 'validSince' => 100, 'validUntil' => 200, 'type' => 'TEXT', 'bytes' => base64_encode('older')],
                ],
                'paginator' => ['totalCount' => 2, 'totalPages' => 1, 'page' => 0, 'size' => 50],
            ], 200),
        ]);

        $list = $this->makeProvider()->backups($this->makeDevice());

        $this->assertSame('2', $list['backups'][0]['id']);
        $this->assertSame('0', $list['backups'][1]['id'], 'zero-based ids survive stringification');
        $this->assertSame(2, $list['total']);
    }

    public function testLatestStringifiesIdAndIncludesContent(): void
    {
        Http::fake([
            'unimus:8085/api/v2/devices/findByAddress/*' => Http::response(['data' => ['id' => 7]], 200),
            'unimus:8085/api/v2/devices/7/backups/latest' => Http::response([
                'data' => ['id' => 3, 'validSince' => 300, 'validUntil' => null, 'type' => 'TEXT', 'bytes' => base64_encode('latest config')],
            ], 200),
        ]);

        $latest = $this->makeProvider()->latest($this->makeDevice());

        $this->assertSame('3', $latest['id']);
        $this->assertSame('latest config', $latest['content']);
    }

    public function testContentRejectsNonNumericId(): void
    {
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        $provider = $this->makeProvider();

        $this->assertNull($provider->content($this->makeDevice(), 'a1b2c3d'));
        $this->assertSame(ConfigBackupProvider::ERROR_BACKUP_NOT_FOUND, $provider->lastError());
        Http::assertNothingSent();
    }

    public function testDiffRejectsNonNumericIds(): void
    {
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        $provider = $this->makeProvider();

        $this->assertNull($provider->diff($this->makeDevice(), 'abc', '2'));
        $this->assertSame(ConfigBackupProvider::ERROR_BACKUP_NOT_FOUND, $provider->lastError());
        Http::assertNothingSent();
    }

    public function testDeviceNotFoundErrorPassthrough(): void
    {
        Http::fake(['*' => Http::response(['code' => 404], 404)]);

        $provider = $this->makeProvider();

        $this->assertNull($provider->backups($this->makeDevice()));
        $this->assertSame(ConfigBackupProvider::ERROR_DEVICE_NOT_FOUND, $provider->lastError());
    }

    public function testUnreachableErrorPassthrough(): void
    {
        Http::fake(function (): void {
            throw new ConnectionException('cURL error 7: Failed to connect');
        });

        Log::shouldReceive('warning')->atLeast()->once();

        $provider = $this->makeProvider();

        $this->assertNull($provider->backups($this->makeDevice()));
        $this->assertSame(ConfigBackupProvider::ERROR_UNREACHABLE, $provider->lastError());
    }

    public function testLastErrorResetsBetweenCalls(): void
    {
        Http::fake([
            'unimus:8085/api/v2/devices/findByAddress/*' => Http::response(['data' => ['id' => 7]], 200),
            'unimus:8085/api/v2/devices/7/backups*' => Http::response([
                'data' => [['id' => 1, 'validSince' => 100, 'validUntil' => null, 'type' => 'TEXT', 'bytes' => base64_encode('config')]],
                'paginator' => ['totalCount' => 1, 'totalPages' => 1, 'page' => 0, 'size' => 50],
            ], 200),
        ]);

        $provider = $this->makeProvider();

        $this->assertNull($provider->content($this->makeDevice(), 'bogus'));
        $this->assertNotNull($provider->lastError());

        $this->assertNotNull($provider->backups($this->makeDevice()));
        $this->assertNull($provider->lastError());
    }
}
