<?php

namespace LibreNMS\Tests\Unit\View;

use App\Models\Device;
use App\Models\Mempool;
use App\View\Components\Device\Overview\Memory;
use Illuminate\Database\Eloquent\Collection;
use LibreNMS\Tests\TestCase;

final class DeviceOverviewMemoryTest extends TestCase
{
    public function testPercentageOnlyPoolsDoNotDisplayByteUnits(): void
    {
        foreach ([100, '100'] as $total) {
            $device = new Device;
            $device->setRelation('mempools', new Collection([
                new Mempool([
                    'mempool_class' => 'system',
                    'mempool_total' => $total,
                    'mempool_used' => 7,
                    'mempool_free' => 93,
                    'mempool_perc' => 7,
                ]),
            ]));

            $data = (new Memory($device))->mempools[0];
            $this->assertSame('7%', $data['leftText']);
            $this->assertSame('93%', $data['rightText']);
            $this->assertSame('100%', $data['total']);
            $this->assertSame('7%', $data['used']);
            $this->assertSame('93%', $data['free']);
            $this->assertSame(7.0, $data['percent']);
        }
    }

    public function testBytePoolsRetainSizesAndBufferCacheShadow(): void
    {
        $device = new Device;
        $device->setRelation('mempools', new Collection([
            new Mempool([
                'mempool_class' => 'system',
                'mempool_total' => 1024 * 1024 * 1024,
                'mempool_used' => 256 * 1024 * 1024,
                'mempool_free' => 768 * 1024 * 1024,
                'mempool_perc' => 25,
            ]),
            new Mempool(['mempool_class' => 'buffers', 'mempool_used' => 128 * 1024 * 1024]),
            new Mempool(['mempool_class' => 'cached', 'mempool_used' => 128 * 1024 * 1024]),
        ]));

        $data = (new Memory($device))->mempools[0];
        $this->assertSame('256 MiB / 1 GiB (25%)', $data['leftText']);
        $this->assertSame('768 MiB', $data['rightText']);
        $this->assertEquals(50, $data['shadow']);
    }
}
