<?php

namespace LibreNMS\Tests\Feature\Http\Search;

use App\Models\Ipv4Mac;
use App\Models\User;
use App\Models\UserPref;
use Illuminate\Support\Facades\DB;

class HostExportTest extends SearchTestCase
{
    /**
     * @param  array<string, mixed>  $params
     * @return list<array<string, string>>
     */
    private function exported(array $params, ?User $user = null): array
    {
        $response = $this->actingAs($user ?? $this->admin())->get(route('search.export', $params))->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));

        $lines = array_map(fn (string $line): array => str_getcsv($line, ',', '"', '\\'), explode("\n", trim(ltrim($response->streamedContent(), "\xEF\xBB\xBF"))));
        $headers = array_shift($lines);

        return array_map(fn (array $line): array => array_combine($headers, $line), $lines);
    }

    public function testExportHasWhereEachHostIs(): void
    {
        [, $routerPort, $switch, $switchPort] = $this->hostBehindSwitch('10.87.1.5', '02c9c9c9c001');
        Ipv4Mac::factory()->create(['device_id' => $routerPort->device_id, 'port_id' => $routerPort->port_id, 'mac_address' => '02c9c9c9c002', 'ipv4_address' => '10.87.1.6']);

        $rows = $this->exported(['q' => '10.87.1.0/24', 'export' => 'all']);

        $this->assertSame(['10.87.1.5', '10.87.1.6'], array_column($rows, __('search.export.ip')));
        $this->assertSame('02:c9:c9:c9:c0:01', $rows[0][__('search.export.mac')]);
        $this->assertSame($switch->display, $rows[0][__('search.export.switch')]);
        $this->assertSame($switchPort->getLabel(), $rows[0][__('search.export.port')]);
        $this->assertSame('1', $rows[0][__('search.export.macs_on_port')]);
        $this->assertNotSame('', $rows[0][__('search.export.last_seen')]);
        // no forwarding entry, the router that knows it is all there is
        $this->assertSame('', $rows[1][__('search.export.switch')]);
        $this->assertStringContainsString($routerPort->getLabel(), $rows[1][__('search.export.seen_via')]);
    }

    public function testExportAllIgnoresPagesAndExportPageKeepsToOne(): void
    {
        [$router, $port] = $this->portOn();
        foreach (range(1, 12) as $last) {
            Ipv4Mac::factory()->create(['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => sprintf('02cacacac0%02d', $last), 'ipv4_address' => "10.86.1.$last"]);
        }

        $this->assertCount(12, $this->exported(['q' => '10.86.1.0/24', 'export' => 'all', 'page' => 2, 'perPage' => 10]));

        $page = $this->exported(['q' => '10.86.1.0/24', 'export' => 'page', 'page' => 2, 'perPage' => 10]);
        $this->assertSame(['10.86.1.11', '10.86.1.12'], array_column($page, __('search.export.ip')));
    }

    public function testExportFollowsFiltersSortAndMacFormat(): void
    {
        $admin = $this->admin();
        UserPref::setPref($admin, 'network_search_mac_format', 'dot');
        [, , $switch] = $this->hostBehindSwitch('10.85.1.5', '02cbcbcbc001');
        $this->hostBehindSwitch('10.85.1.6', '02cbcbcbc002');

        $filtered = $this->exported(['q' => '10.85.1.0/24', 'filter' => ['device_id' => ['eq' => $switch->device_id]]], $admin);
        $this->assertSame(['10.85.1.5'], array_column($filtered, __('search.export.ip')));
        $this->assertSame('02cb.cbcb.c001', $filtered[0][__('search.export.mac')]);

        $sorted = $this->exported(['q' => '10.85.1.0/24', 'sort' => 'ip', 'dir' => 'desc'], $admin);
        $this->assertSame(['10.85.1.6', '10.85.1.5'], array_column($sorted, __('search.export.ip')));
    }

    public function testExportHasARowForEachMacOfAnAddressAndAllIpsOfAMac(): void
    {
        [$first, $firstPort] = $this->portOn();
        [$second, $secondPort] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $first->device_id, 'port_id' => $firstPort->port_id, 'mac_address' => '02cccccc0001', 'ipv4_address' => '10.84.2.5']);
        Ipv4Mac::factory()->create(['device_id' => $second->device_id, 'port_id' => $secondPort->port_id, 'mac_address' => '02cccccc0002', 'ipv4_address' => '10.84.2.5']);
        Ipv4Mac::factory()->create(['device_id' => $second->device_id, 'port_id' => $secondPort->port_id, 'mac_address' => '02cddddd0001', 'ipv4_address' => '10.84.2.6']);
        Ipv4Mac::factory()->create(['device_id' => $second->device_id, 'port_id' => $secondPort->port_id, 'mac_address' => '02cddddd0001', 'ipv4_address' => '10.84.2.7']);

        // the address nothing knows about still has a row
        $this->assertSame(['02:cc:cc:cc:00:01', '02:cc:cc:cc:00:02', ''], array_column($this->exported(['q' => '10.84.2.5 10.84.2.99']), __('search.export.mac')));
        $this->assertSame(['10.84.2.6 10.84.2.7'], array_column($this->exported(['q' => '02:cd:dd']), __('search.export.ip')));
    }

    public function testExportRespectsAccess(): void
    {
        [$hidden, $hiddenPort] = $this->portOn();
        Ipv4Mac::factory()->create(['device_id' => $hidden->device_id, 'port_id' => $hiddenPort->port_id, 'mac_address' => '02cecececc01', 'ipv4_address' => '10.83.2.5']);
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->assertSame([], $this->exported(['q' => '10.83.2.0/24'], $user));
    }

    public function testExportNeutralizesFormulas(): void
    {
        [, , , $switchPort] = $this->hostBehindSwitch('10.72.1.5', '02e5e5e5e001');
        $switchPort->update(['ifAlias' => '=HYPERLINK("http://example.test")']);

        $rows = $this->exported(['q' => '10.72.1.5 10.72.1.6']);

        $this->assertSame('\'=HYPERLINK("http://example.test")', $rows[0][__('search.export.port_description')]);
    }

    public function testExportAllRunsTheListQueryOnce(): void
    {
        [$router, $port] = $this->portOn();
        Ipv4Mac::insert(array_map(fn (int $i): array => ['device_id' => $router->device_id, 'port_id' => $port->port_id, 'mac_address' => sprintf('02e7e7e7%04x', $i), 'ipv4_address' => '10.69.' . intdiv($i, 256) . '.' . ($i % 256), 'context_name' => ''], range(1, 501)));

        DB::enableQueryLog();
        $rows = $this->exported(['q' => '10.69.0.0/16', 'export' => 'all']);

        $this->assertCount(501, $rows);
        $this->assertCount(1, array_filter(DB::getQueryLog(), fn (array $query): bool => str_contains($query['query'], 'as `hosts`')));
    }
}
