<?php

namespace LibreNMS\Tests\Unit\Scan;

use LibreNMS\Enum\ScanOutcome;
use LibreNMS\Scan\NetworkScanner;
use LibreNMS\Scan\ScanOptions;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('NetworkScanner Unit Tests')]
final class NetworkScannerTest extends TestCase
{
    #[TestDox('ScanOptions correctly creates from default configuration')]
    public function testScanOptionsDefaults(): void
    {
        $options = ScanOptions::fromConfig(
            pollerGroup: '5',
            dnsOnly: true,
            pingFallback: true,
            pingOnly: false,
        );

        $this->assertSame('5', $options->pollerGroup);
        $this->assertTrue($options->dnsOnly);
        $this->assertTrue($options->pingFallback);
        $this->assertFalse($options->pingOnly);
    }

    #[TestDox('NetworkScanner handles empty IP list')]
    public function testEmptyIpList(): void
    {
        $options = new ScanOptions(icmpEnabled: false);
        $scanner = new NetworkScanner();

        $results = [];
        $scanner->scan([], $options, 4, function (string $ip, ScanOutcome $outcome) use (&$results): void {
            $results[$ip] = $outcome;
        });

        $this->assertEmpty($results);
    }
}
