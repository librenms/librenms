<?php

namespace LibreNMS\Tests\Unit\RRD;

use LibreNMS\RRD\PortRrd;
use LibreNMS\Tests\TestCase;

final class PortRrdTest extends TestCase
{
    public function testTuneLimits(): void
    {
        $this->assertSame([], PortRrd::tuneLimits(9999999));

        $limits = PortRrd::tuneLimits(1000000000);
        $this->assertSame([
            'INOCTETS',
            'OUTOCTETS',
            'INERRORS',
            'OUTERRORS',
            'INUCASTPKTS',
            'OUTUCASTPKTS',
            'INNUCASTPKTS',
            'OUTNUCASTPKTS',
            'INDISCARDS',
            'OUTDISCARDS',
            'INUNKNOWNPROTOS',
            'INBROADCASTPKTS',
            'OUTBROADCASTPKTS',
            'INMULTICASTPKTS',
            'OUTMULTICASTPKTS',
        ], array_keys($limits));
        $this->assertEquals(['max' => 125000000], $limits['INOCTETS']);
    }
}
