<?php

/**
 * UnimusApiClientTest.php
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
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use LibreNMS\Tests\TestCase;

final class UnimusApiClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('unimus.enabled', true);
        LibrenmsConfig::set('unimus.url', 'http://unimus:8085');
        LibrenmsConfig::set('unimus.api_version', 'v2');
        LibrenmsConfig::set('unimus.token', 'test-token');
    }

    private function makeDevice(): Device
    {
        $device = new Device(['hostname' => 'router.example.com']);
        $device->device_id = 42;

        return $device;
    }

    public function testFindDeviceIdReturnsIdOnFirstCandidate(): void
    {
        Http::fake([
            'unimus:8085/api/v2/devices/findByAddress/*' => Http::response(['data' => ['id' => 7]], 200),
        ]);

        $client = new Unimus();
        $this->assertSame(7, $client->findDeviceId($this->makeDevice()));

        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'findByAddress/router.example.com')
            && $request->hasHeader('Authorization', 'Bearer test-token'));
    }

    public function testFindDeviceIdFallsThroughCandidatesOn404(): void
    {
        Http::fake([
            'unimus:8085/api/v2/devices/findByAddress/router.example.com' => Http::response(['code' => 404], 404),
            'unimus:8085/api/v2/devices/findByAddress/router' => Http::response(['data' => ['id' => 9]], 200),
        ]);

        $client = new Unimus();
        $this->assertSame(9, $client->findDeviceId($this->makeDevice()));
        Http::assertSentCount(2);
    }

    public function testFindDeviceIdReturnsNullWhenAllCandidates404(): void
    {
        Http::fake(['*' => Http::response(['code' => 404], 404)]);

        $client = new Unimus();
        $this->assertNull($client->findDeviceId($this->makeDevice()));
    }

    public function testFindDeviceIdReturnsNullOnConnectionException(): void
    {
        Http::fake(function (): void {
            throw new ConnectionException('cURL error 7: Failed to connect');
        });

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(fn ($msg) => str_contains((string) $msg, 'Unimus is not reachable'));

        $client = new Unimus();
        $this->assertNull($client->findDeviceId($this->makeDevice()));
    }

    public function testFindDeviceIdStopsOnAuthError(): void
    {
        Http::fake(['*' => Http::response(['code' => 401], 401)]);

        $client = new Unimus();
        $this->assertNull($client->findDeviceId($this->makeDevice()));
        $this->assertSame('error', $client->lastError());
        Http::assertSentCount(1);
    }

    public function testClientSendsNothingWhenDisabled(): void
    {
        LibrenmsConfig::set('unimus.enabled', false);

        Http::fake(['*' => Http::response(['data' => ['id' => 7]], 200)]);

        $client = new Unimus();
        $this->assertNull($client->findDeviceId($this->makeDevice()));
        $this->assertNull($client->getLatestBackup(7));
        $this->assertNull($client->getBackups(7));
        $this->assertNull($client->getDiff(1, 2));

        Http::assertNothingSent();
    }

    public function testGetLatestBackupDecodesContent(): void
    {
        Http::fake([
            'unimus:8085/api/v2/devices/7/backups/latest' => Http::response([
                'data' => [
                    'id' => 3,
                    'validSince' => 1525117527,
                    'validUntil' => null,
                    'type' => 'TEXT',
                    'bytes' => base64_encode("hostname router\ninterface eth0"),
                ],
            ], 200),
        ]);

        $client = new Unimus();
        $backup = $client->getLatestBackup(7);

        $this->assertSame(3, $backup['id']);
        $this->assertSame(1525117527, $backup['date']);
        $this->assertSame('TEXT', $backup['type']);
        $this->assertSame("hostname router\ninterface eth0", $backup['content']);
    }

    public function testGetLatestBackupReturnsNullContentForBinary(): void
    {
        Http::fake([
            '*' => Http::response([
                'data' => [
                    'id' => 4,
                    'validSince' => 1525117527,
                    'type' => 'BINARY',
                    'bytes' => base64_encode("\x00\x01\x02"),
                ],
            ], 200),
        ]);

        $client = new Unimus();
        $backup = $client->getLatestBackup(7);

        $this->assertSame('BINARY', $backup['type']);
        $this->assertNull($backup['content']);
    }

    public function testGetBackupsStripsContentAndReturnsPaginator(): void
    {
        Http::fake([
            'unimus:8085/api/v2/devices/7/backups*' => Http::response([
                'data' => [
                    ['id' => 2, 'validSince' => 200, 'validUntil' => null, 'type' => 'TEXT', 'bytes' => base64_encode('newer config')],
                    ['id' => 1, 'validSince' => 100, 'validUntil' => 200, 'type' => 'TEXT', 'bytes' => base64_encode('older config')],
                ],
                'paginator' => ['totalCount' => 2, 'totalPages' => 1, 'page' => 0, 'size' => 50],
            ], 200),
        ]);

        $client = new Unimus();
        $result = $client->getBackups(7);

        $this->assertCount(2, $result['backups']);
        $this->assertSame(2, $result['total']);
        $this->assertSame(1, $result['totalPages']);
        $this->assertSame(0, $result['page']);
        $this->assertNull($result['backups'][0]['content'], 'list metadata should not include content');

        // content is fetched on demand from the page the backup is listed on
        $this->assertSame('newer config', $client->getBackupContent(7, 2));
        $this->assertNull($client->getBackupContent(7, 999), 'unknown backup id should return null');
    }

    public function testGetDiffNormalizesGroups(): void
    {
        // response shape verified against a live Unimus 2.x instance
        Http::fake([
            'unimus:8085/api/v2/backups/diff*' => Http::response([
                'data' => [
                    'origDeviceInfo' => ['id' => 0, 'address' => '192.168.1.1', 'type' => 'IOS'],
                    'revDeviceInfo' => ['id' => 0, 'address' => '192.168.1.1', 'type' => 'IOS'],
                    'lineGroups' => [
                        [
                            'type' => 'COMMON',
                            'originalLines' => [['number' => 1, 'text' => 'hostname router']],
                            'revisedLines' => [['number' => 1, 'text' => 'hostname router']],
                        ],
                        [
                            'type' => 'CHANGED',
                            'originalLines' => [['number' => 2, 'text' => 'mtu 1500']],
                            'revisedLines' => [['number' => 2, 'text' => 'mtu 9000']],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $client = new Unimus();
        $diff = $client->getDiff(1, 2);

        $this->assertCount(2, $diff);
        $this->assertSame('COMMON', $diff[0]['type']);
        $this->assertSame('CHANGED', $diff[1]['type']);
        $this->assertSame(['line' => 2, 'text' => 'mtu 1500'], $diff[1]['original'][0]);
        $this->assertSame(['line' => 2, 'text' => 'mtu 9000'], $diff[1]['revised'][0]);

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'origId=1') && str_contains((string) $request->url(), 'revId=2'));
    }
}
