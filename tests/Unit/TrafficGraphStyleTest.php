<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Data\Graphing\TrafficGraphStyle;
use PHPUnit\Framework\TestCase;

class TrafficGraphStyleTest extends TestCase
{
    public function testOverlappingDirectionsUseDistinctStrokesWithoutChangingDataOrStacking(): void
    {
        $options = [
            'CDEF:outbits0_neg=outbits0,1,*',
            'AREA:inbits0#CDEB8B88:In',
            'AREA:inbits1#CDEB8B88::STACK',
            'AREA:outbits0_neg#C3D9FF88:',
            'AREA:outbits1_neg#C3D9FF88::STACK',
            'HRULE:999999999999999#C3D9FF:Out',
            'GPRINT:outbits0:LAST:%6.2lf%s',
            'LINE1:percentile_in#aa0000',
            'AREA:mempoolused#123456:Memory',
            'HRULE:0#C3D9FF:',
        ];
        $styled = TrafficGraphStyle::sameAxis($options);
        $this->assertSame('LINE2:inbits0#00A6ED:In', $styled[1]);
        $this->assertSame('LINE2:inbits1#00A6ED::STACK', $styled[2]);
        $this->assertSame('LINE2:outbits0_neg#FF8C00::dashes=6,3', $styled[3]);
        $this->assertSame('LINE2:outbits1_neg#FF8C00::STACK:dashes=6,3', $styled[4]);
        $this->assertSame('HRULE:999999999999999#FF8C00:Out', $styled[5]);
        foreach ([0, 6, 7, 8, 9] as $index) {
            $this->assertSame($options[$index], $styled[$index]);
        }
    }

    public function testHistoricalAndMaximumLinesAreSecondaryAndStylingIsIdempotent(): void
    {
        $options = TrafficGraphStyle::sameAxis(['AREA:inbitsX#9999999988:', 'LINE1.25:doutbits_max#8080C0:Out']);
        $this->assertSame('LINE1:inbitsX#00A6ED88::dashes=2,4', $options[0]);
        $this->assertSame('LINE1:doutbits_max#FF8C0088:Out:dashes=6,3', $options[1]);
        $this->assertSame($options, TrafficGraphStyle::sameAxis($options));
    }
}
