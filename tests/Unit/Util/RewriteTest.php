<?php

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Rewrite;
use PHPUnit\Framework\Attributes\DataProvider;

final class RewriteTest extends TestCase
{
    public function testBgpErrorCode(): void
    {
        $this->assertSame('Cease - Administrative Shutdown', Rewrite::bgpErrorCode(6, 2));
        $this->assertSame('Cease - Administrative Shutdown', Rewrite::bgpErrorCode('6', '2'));
        $this->assertSame('Hold Timer Expired', Rewrite::bgpErrorCode(4, 0));
        $this->assertSame('Hold Timer Expired', Rewrite::bgpErrorCode(4, 99));
        $this->assertSame('Unknown', Rewrite::bgpErrorCode(99, 0));
        $this->assertSame('Unknown', Rewrite::bgpErrorCode('', ''));
    }

    #[DataProvider('processorDescrProvider')]
    public function testProcessorDescr(?string $descr, string $expected): void
    {
        $this->assertSame($expected, Rewrite::processorDescr($descr));
    }

    /**
     * @return array<string, array{?string, string}>
     */
    public static function processorDescrProvider(): array
    {
        return [
            'intel hr device' => ['GenuineIntel: Intel(R) Core(TM) i7-8700 CPU @ 3.20GHz', 'Core i7-8700 @ 3.20GHz'],
            'amd hr device' => ['AuthenticAMD: AMD EPYC 7502P 32-Core Processor', 'AMD EPYC 7502P 32-Core Processor'],
            'lowercase trademark' => ['Intel(R) Xeon(R) CPU E5-2620 v4 (tm)', 'Xeon E5-2620 v4'],
            'cpu inside a name is kept' => ['module 0/RSP0/CPU0', 'module 0/RSP0/CPU0'],
            'collapses whitespace' => ["  QFP   0 \t Engine ", 'QFP 0 Engine'],
            'unchanged' => ['Processor', 'Processor'],
            'null' => [null, ''],
        ];
    }
}
