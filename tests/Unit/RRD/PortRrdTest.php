<?php

namespace LibreNMS\Tests\Unit\RRD;

use App\Data\TimeSeries\MetricIdentity;
use App\Facades\LibrenmsConfig;
use LibreNMS\RRD\PortRrd;
use LibreNMS\Tests\TestCase;

final class PortRrdTest extends TestCase
{
    public function testTuneLimits(): void
    {
        $this->assertSame([], PortRrd::tuneLimits($this->port(), 9999999));

        $limits = PortRrd::tuneLimits($this->port(), 1000000000);
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

    public function testCanonicalFilesAreTunedByFieldName(): void
    {
        LibrenmsConfig::set('datastore.canonical_metrics', true);

        $limits = PortRrd::tuneLimits($this->port(), 1000000000);
        $this->assertEquals(['max' => 125000000], $limits['in_octets']);
        $this->assertArrayNotHasKey('INOCTETS', $limits);
    }

    private function port(): MetricIdentity
    {
        return new MetricIdentity('port', ['hostname' => 'localhost', 'id' => 12]);
    }
}
