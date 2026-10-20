<?php

namespace LibreNMS\Tests\Unit\Scan;

use LibreNMS\Scan\PingBatch;
use LibreNMS\Tests\TestCase;
use LibreNMS\Tests\Traits\FakesNetworkTools;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('PingBatch')]
final class PingBatchTest extends TestCase
{
    use FakesNetworkTools;

    #[TestDox('Reads each result once, with one fping process for the batch')]
    public function testResults(): void
    {
        $log = $this->fakeFping(['192.0.2.1', '2001:db8::1']);
        $batch = new PingBatch(['192.0.2.1', '192.0.2.2', '2001:db8::1']);

        $this->assertTrue($this->wait($batch, '192.0.2.1'));
        $this->assertFalse($this->wait($batch, '192.0.2.2'));
        $this->assertTrue($this->wait($batch, '2001:db8::1'));
        // pending until fping finishes, then not alive
        $this->assertFalse($this->wait($batch, '192.0.2.1'), 'a result is read once');
        $this->assertFalse($this->wait($batch, '192.0.2.99'), 'an address not in the batch is not alive');
        $this->assertSame(['3'], file($log, FILE_IGNORE_NEW_LINES));
    }

    private function wait(PingBatch $batch, string $ip): bool
    {
        while (($result = $batch->result($ip)) === null) {
            $batch->poll();
            usleep(1000);
        }

        return $result;
    }
}
