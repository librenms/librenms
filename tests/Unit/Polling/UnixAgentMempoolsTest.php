<?php

namespace LibreNMS\Tests\Unit\Polling;

use App\Models\Mempool;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use LibreNMS\Polling\Providers\UnixAgent\UnixAgentMempools;
use LibreNMS\Tests\TestCase;

final class UnixAgentMempoolsTest extends TestCase
{
    private const OUTPUT = "<<<uptime>>>\n12345.67 999\n<<<mem>>>\nMemTotal:       16000000 kB\nMemFree:         4000000 kB\nMemAvailable:   10000000 kB\nBuffers:          500000 kB\nCached:          3000000 kB\nSwapTotal:       2000000 kB\nSwapFree:        1500000 kB\n";

    public function testWithoutMemSectionThereIsNoProvider(): void
    {
        $this->assertNull(UnixAgentMempools::fromOutput("<<<uptime>>>\n12345.67 999\n"));
        $this->assertNull(UnixAgentMempools::fromOutput(''));
    }

    public function testDiscoversMeminfoLikeUcd(): void
    {
        $mempools = UnixAgentMempools::fromOutput(self::OUTPUT)?->discoverMempools()->keyBy('mempool_class');

        $this->assertNotNull($mempools);
        $this->assertSame(['system', 'swap', 'buffers', 'cached', 'available'], $mempools->keys()->all());
        $this->assertSame(['unix-agent'], $mempools->pluck('mempool_type')->unique()->values()->all());

        $system = $mempools['system'];
        $this->assertEquals(16000000 * 1024, $system->mempool_total);
        $this->assertEquals(12000000 * 1024, $system->mempool_used);
        $this->assertEquals(75, $system->mempool_perc);

        $this->assertEquals(500000 * 1024, $mempools['buffers']->mempool_used);
        $this->assertEquals(6000000 * 1024, $mempools['available']->mempool_used);
        $this->assertEquals(25, $mempools['swap']->mempool_perc);
    }

    public function testSkipsMissingFields(): void
    {
        $mempools = UnixAgentMempools::fromOutput("<<<mem>>>\nMemTotal: 1000 kB\nMemFree: 500 kB\n")?->discoverMempools();

        $this->assertSame(['system'], $mempools?->pluck('mempool_class')->all());
    }

    public function testPollUpdatesUsage(): void
    {
        $mempool = new Mempool(['mempool_index' => 1, 'mempool_type' => 'unix-agent', 'mempool_class' => 'system', 'mempool_precision' => 1024]);

        UnixAgentMempools::fromOutput(self::OUTPUT)?->pollMempools(new EloquentCollection([$mempool]));

        $this->assertEquals(4000000 * 1024, $mempool->mempool_free);
        $this->assertEquals(75, $mempool->mempool_perc);
    }
}
