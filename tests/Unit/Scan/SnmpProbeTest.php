<?php

namespace LibreNMS\Tests\Unit\Scan;

use LibreNMS\Scan\SnmpProbe;
use LibreNMS\Tests\TestCase;
use LibreNMS\Tests\Traits\FakesNetworkTools;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('SnmpProbe')]
final class SnmpProbeTest extends TestCase
{
    use FakesNetworkTools;

    /**
     * @param  array<int, string>  $communities
     */
    #[DataProvider('credentialProvider')]
    #[TestDox('Picks the most preferred credential that replies: $_dataName')]
    public function testPreference(array $communities, ?int $expected): void
    {
        $this->fakeSnmpget();
        $probe = new SnmpProbe('192.0.2.1', $this->fakeSnmpConfigs($communities));

        while (! $probe->settled()) {
            usleep(1000);
        }

        $this->assertSame($expected, $probe->secretId());
    }

    /**
     * @return array<string, array{array<int, string>, ?int}>
     */
    public static function credentialProvider(): array
    {
        return [
            'waits for a slower preferred credential' => [[10 => '0-1', 1 => '0.3-0', 2 => '0-0'], 1],
            'skips credentials without a reply' => [[1 => '0.2-1', 2 => '0-0'], 2],
            'an error response is a reply' => [[1 => '0-2'], 1],
            'no reply' => [[1 => '0-1', 2 => '0-1'], null],
        ];
    }

    #[TestDox('Without credentials it is settled with no reply')]
    public function testNoCredentials(): void
    {
        $probe = new SnmpProbe('192.0.2.1', []);

        $this->assertTrue($probe->settled());
        $this->assertNull($probe->secretId());
    }
}
