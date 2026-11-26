<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\Port;
use App\Models\PortStatistic;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

final class PortDiscardsFilterTest extends DBTestCase
{
    use DatabaseTransactions;

    private Device $device;

    /** @var array<string, Port> */
    private array $ports = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = Device::factory()->create();

        $this->ports = [
            'in_discards' => $this->port([10, 0]),
            'out_discards' => $this->port([0, 600]),
            'errors_only' => $this->port([0, 0], ['ifInErrors_delta' => 5, 'ifOutErrors_delta' => 5]),
            'clean' => $this->port([0, 0]),
            'no_statistics' => $this->port(null),
            'ignored' => $this->port([0, 3], ['ignore' => 1]),
            'disabled' => $this->port([0, 3], ['disabled' => 1]),
        ];
    }

    public function testHasDiscardsMatchesBothDirections(): void
    {
        $this->assertSame(['in_discards', 'out_discards'], $this->names(Port::where('device_id', $this->device->device_id)->hasDiscards()));
    }

    public function testDiscardsFilterYes(): void
    {
        $this->assertSame(
            ['disabled', 'ignored', 'in_discards', 'out_discards'],
            $this->names(Port::where('device_id', $this->device->device_id)->applyFilters(['discards' => ['eq' => '1']]))
        );
    }

    public function testDiscardsFilterNo(): void
    {
        $this->assertSame(
            ['clean', 'errors_only', 'no_statistics'],
            $this->names(Port::where('device_id', $this->device->device_id)->applyFilters(['discards' => ['eq' => '0']]))
        );
    }

    public function testErrorsFilterIgnoresDiscards(): void
    {
        $this->assertSame(['errors_only'], $this->names(Port::where('device_id', $this->device->device_id)->hasErrors()));
    }

    public function testPortsTableShowsDiscardRates(): void
    {
        $rows = $this->actingAs(User::factory()->admin()->create(['enabled' => true]))
            ->postJson(route('table.ports'), [
                'device_id' => $this->device->device_id,
                'filter' => ['discards' => ['eq' => '1'], 'ignore' => ['eq' => '0'], 'disabled' => ['eq' => '0']],
                'sort' => ['ifOutDiscards_delta' => 'desc'],
            ])
            ->assertOk()
            ->json('rows');

        $this->assertCount(2, $rows);
        $this->assertSame('2 PPS', $rows[0]['ifOutDiscards_delta']);
        $this->assertSame('0 PPS', $rows[0]['ifInDiscards_delta']);
        $this->assertSame('33.33 mPPS', $rows[1]['ifInDiscards_delta']);
    }

    public function testPortsPageColumnVisibility(): void
    {
        // [query, errors columns visible, discards columns visible]
        $cases = [
            [['view' => 'detail', 'filter' => ['errors' => ['eq' => '1']]], 'true', 'false'],
            [['view' => 'detail', 'filter' => ['discards' => ['eq' => '1']]], 'false', 'true'],
            [['view' => 'detail'], 'true', 'true'],
            [[], 'false', 'false'],
        ];

        $user = User::factory()->admin()->create(['enabled' => true]);

        foreach ($cases as [$query, $errors, $discards]) {
            $this->actingAs($user)->get(route('ports', $query))
                ->assertOk()
                ->assertSee('data-column-id="ifInErrors_delta" data-searchable="false" data-visible="' . $errors . '"', false)
                ->assertSee('data-column-id="ifInDiscards_delta" data-searchable="false" data-visible="' . $discards . '"', false);
        }
    }

    /**
     * @param  array{int, int}|null  $discards  in/out discard deltas, null to create the port without a ports_statistics row
     * @param  array<string, mixed>  $attributes
     */
    private function port(?array $discards, array $attributes = []): Port
    {
        $port = Port::factory()->create($attributes + [
            'device_id' => $this->device->device_id,
            'poll_period' => 300,
            'ifInErrors_delta' => 0,
            'ifOutErrors_delta' => 0,
        ]);

        if ($discards !== null) {
            PortStatistic::factory()->create([
                'port_id' => $port->port_id,
                'ifInDiscards_delta' => $discards[0],
                'ifOutDiscards_delta' => $discards[1],
            ]);
        }

        return $port;
    }

    /**
     * @param  Builder<Port>  $query
     * @return list<string>
     */
    private function names(Builder $query): array
    {
        $ids = $query->pluck('port_id')->all();

        return collect($this->ports)
            ->filter(fn (Port $port) => in_array($port->port_id, $ids))
            ->keys()
            ->sort()
            ->values()
            ->all();
    }
}
