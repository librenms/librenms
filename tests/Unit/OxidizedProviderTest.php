<?php

/**
 * OxidizedProviderTest.php
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

use App\ApiClients\Oxidized;
use App\ConfigBackup\Providers\OxidizedProvider;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DeviceAttrib;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use LibreNMS\Interfaces\ConfigBackupProvider;
use LibreNMS\Tests\TestCase;

final class OxidizedProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('oxidized.enabled', true);
        LibrenmsConfig::set('oxidized.url', 'http://oxidized:8888');
    }

    private function makeProvider(): OxidizedProvider
    {
        return new OxidizedProvider(new Oxidized());
    }

    private function makeDevice(): Device
    {
        $device = new Device(['hostname' => 'router.example.com']);
        $device->device_id = 42;
        // avoid hitting the database for attrib lookups in supportsDevice()
        $device->setRelation('attribs', collect());

        return $device;
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function fakeNode(array $extra = []): array
    {
        return array_merge([
            'name' => 'router.example.com',
            'full_name' => 'router.example.com',
            'ip' => '192.168.1.1',
            'group' => null,
            'model' => 'ios',
            'last' => ['status' => 'success'],
        ], $extra);
    }

    public function testIsConfiguredDelegatesToClient(): void
    {
        $this->assertTrue(OxidizedProvider::isConfigured());

        LibrenmsConfig::set('oxidized.enabled', false);
        $this->assertFalse(OxidizedProvider::isConfigured());
    }

    public function testSupportsDeviceHonoursIgnoreLists(): void
    {
        $device = $this->makeDevice();
        $device->os = 'ios';

        $this->assertTrue($this->makeProvider()->supportsDevice($device));

        LibrenmsConfig::set('oxidized.ignore_os', ['ios']);
        $this->assertFalse($this->makeProvider()->supportsDevice($device));
    }

    public function testSupportsDeviceHonoursDisableAttrib(): void
    {
        $device = $this->makeDevice();
        $device->setRelation('attribs', collect([
            new DeviceAttrib(['attrib_type' => 'override_Oxidized_disable', 'attrib_value' => 'true']),
        ]));

        $this->assertFalse($this->makeProvider()->supportsDevice($device));
    }

    public function testBackupsMapsVersions(): void
    {
        Http::fake([
            'oxidized:8888/node/show/*' => Http::response($this->fakeNode(), 200),
            'oxidized:8888/node/version?*' => Http::response([
                ['oid' => 'aaaa1111', 'date' => '2026-07-14 00:24:24 +0000', 'message' => 'update'],
                ['oid' => 'bbbb2222', 'date' => '2026-07-13 00:21:17 +0000', 'message' => 'update'],
            ], 200),
        ]);

        $list = $this->makeProvider()->backups($this->makeDevice());

        $this->assertSame('aaaa1111', $list['backups'][0]['id']);
        $this->assertSame(strtotime('2026-07-14 00:24:24 +0000'), $list['backups'][0]['date']);
        $this->assertSame('TEXT', $list['backups'][0]['type']);
        $this->assertNull($list['backups'][0]['content']);
        $this->assertSame(2, $list['total']);
        $this->assertSame(1, $list['totalPages']);
    }

    public function testLatestIncludesContent(): void
    {
        Http::fake([
            'oxidized:8888/node/show/*' => Http::response($this->fakeNode(), 200),
            'oxidized:8888/node/version?*' => Http::response([
                ['oid' => 'aaaa1111', 'date' => '2026-07-14 00:24:24 +0000'],
            ], 200),
            'oxidized:8888/node/version/view*' => Http::response('hostname router', 200),
        ]);

        $latest = $this->makeProvider()->latest($this->makeDevice());

        $this->assertSame('aaaa1111', $latest['id']);
        $this->assertSame('hostname router', $latest['content']);
    }

    public function testContentFetchesVersion(): void
    {
        Http::fake([
            'oxidized:8888/node/show/*' => Http::response($this->fakeNode(), 200),
            'oxidized:8888/node/version/view*' => Http::response('interface eth0', 200),
        ]);

        $content = $this->makeProvider()->content($this->makeDevice(), 'aaaa1111');

        $this->assertSame('interface eth0', $content);
    }

    public function testContentRejectsNonHexId(): void
    {
        Http::fake(['*' => Http::response('nope', 200)]);

        $provider = $this->makeProvider();

        $this->assertNull($provider->content($this->makeDevice(), 'not-an-oid'));
        $this->assertSame(ConfigBackupProvider::ERROR_BACKUP_NOT_FOUND, $provider->lastError());
        Http::assertNothingSent();
    }

    public function testDiffRejectsNonHexIds(): void
    {
        Http::fake(['*' => Http::response('nope', 200)]);

        $provider = $this->makeProvider();

        $this->assertNull($provider->diff($this->makeDevice(), 'zzz', 'aaaa1111'));
        $this->assertSame(ConfigBackupProvider::ERROR_BACKUP_NOT_FOUND, $provider->lastError());
        Http::assertNothingSent();
    }

    public function testDiffParsesUnifiedDiffIntoGroups(): void
    {
        $diff = <<<'DIFF'
        diff --git a/router b/router
        index abc1234..def5678 100644
        --- a/router
        +++ b/router
        @@ -1,3 +1,3 @@
         hostname router
        -ntp server 1.1.1.1
        +ntp server 2.2.2.2
         end
        DIFF;

        Http::fake([
            'oxidized:8888/node/show/*' => Http::response($this->fakeNode(), 200),
            'oxidized:8888/node/version/diffs*' => Http::response($diff, 200),
        ]);

        $groups = $this->makeProvider()->diff($this->makeDevice(), 'bbbb2222', 'aaaa1111');

        $this->assertSame('COMMON', $groups[0]['type']);
        $this->assertSame('hostname router', $groups[0]['original'][0]['text']);
        $this->assertSame(1, $groups[0]['original'][0]['line']);

        $this->assertSame('DELETED', $groups[1]['type']);
        $this->assertSame('ntp server 1.1.1.1', $groups[1]['original'][0]['text']);
        $this->assertSame(2, $groups[1]['original'][0]['line']);

        $this->assertSame('INSERTED', $groups[2]['type']);
        $this->assertSame('ntp server 2.2.2.2', $groups[2]['revised'][0]['text']);
        $this->assertSame(2, $groups[2]['revised'][0]['line']);

        $this->assertSame('COMMON', $groups[3]['type']);
        $this->assertSame('end', $groups[3]['original'][0]['text']);
    }

    public function testDiffRequestsNewerAsOidOlderAsOid2(): void
    {
        Http::fake([
            'oxidized:8888/node/show/*' => Http::response($this->fakeNode(), 200),
            'oxidized:8888/node/version/diffs*' => Http::response("@@ -1 +1 @@\n-old\n+new\n", 200),
        ]);

        // orig (older) = bbbb2222, rev (newer) = aaaa1111
        $this->makeProvider()->diff($this->makeDevice(), 'bbbb2222', 'aaaa1111');

        Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'oid=aaaa1111')
            && str_contains((string) $request->url(), 'oid2=bbbb2222'));
    }

    public function testNoVersioningFallsBackToCurrentConfig(): void
    {
        Http::fake([
            'oxidized:8888/node/show/*' => Http::response($this->fakeNode(), 200),
            'oxidized:8888/node/version?*' => Http::response('not found', 404),
            'oxidized:8888/node/fetch/*' => Http::response('current config', 200),
        ]);

        $provider = $this->makeProvider();
        $device = $this->makeDevice();

        $list = $provider->backups($device);
        $this->assertSame(1, $list['total']);
        $this->assertSame('current', $list['backups'][0]['id']);

        $this->assertSame('current config', $provider->content($device, 'current'));
    }

    public function testDeviceNotFoundError(): void
    {
        Http::fake(['oxidized:8888/node/show/*' => Http::response('not found', 404)]);

        $provider = $this->makeProvider();

        $this->assertNull($provider->backups($this->makeDevice()));
        $this->assertSame(ConfigBackupProvider::ERROR_DEVICE_NOT_FOUND, $provider->lastError());
    }

    public function testUnreachableError(): void
    {
        Http::fake(function (): void {
            throw new ConnectionException('cURL error 7: Failed to connect');
        });

        Log::shouldReceive('warning')->atLeast()->once();

        $provider = $this->makeProvider();

        $this->assertNull($provider->backups($this->makeDevice()));
        $this->assertSame(ConfigBackupProvider::ERROR_UNREACHABLE, $provider->lastError());
    }
}
