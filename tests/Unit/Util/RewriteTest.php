<?php

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Rewrite;

class RewriteTest extends TestCase
{
    /**
     * @test
     * @dataProvider validMacProvider
     */
    public function testMacToHex(string $from, string $to): void
    {
        $this->assertEquals($to, Rewrite::macToHex($from));
    }

    public function validMacProvider(): array
    {
        return [
            ['00:00:00:00:00:01', '000000000001'],
            ['00-00-00-00-00-01', '000000000001'],
            ['000000.000001',     '000000000001'],
            ['000000000001',      '000000000001'],
            ['00:12:34:ab:cd:ef', '001234abcdef'],
            ['00:12:34:AB:CD:EF', '001234abcdef'],
            ['0:12:34:AB:CD:EF',  '001234abcdef'],
            ['00-12-34-AB-CD-EF', '001234abcdef'],
            ['001234-ABCDEF',     '001234abcdef'],
            ['0012.34AB.CDEF',    '001234abcdef'],
            ['00:02:04:0B:0D:0F', '0002040b0d0f'],
            ['0:2:4:B:D:F',       '0002040b0d0f'],
            ['0:2:4:B:D:F',       '0002040b0d0f'],
            ['80 62 0c 85 25 5c e5 00',  '0c85255ce500'],  // BridgeId format
            ['80620c85255ce500',  '0c85255ce500'],
        ];
    }
}
