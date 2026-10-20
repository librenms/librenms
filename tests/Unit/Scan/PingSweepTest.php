<?php

namespace LibreNMS\Tests\Unit\Scan;

use App\Facades\LibrenmsConfig;
use LibreNMS\Scan\PingSweep;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('PingSweep')]
final class PingSweepTest extends TestCase
{
    private string $fping;
    private string $log;
    private int $read = 0;

    protected function setUp(): void
    {
        parent::setUp();

        // a fake fping, addresses ending in an odd digit are alive, the batch sizes are logged
        $this->fping = (string) tempnam(sys_get_temp_dir(), 'fping');
        $this->log = (string) tempnam(sys_get_temp_dir(), 'fping-log');
        file_put_contents($this->fping, <<<SH
            #!/bin/sh
            count=0
            while read -r ip; do
              count=\$((count + 1))
              case "\$ip" in
                *[13579]) echo "\$ip is alive" ;;
                *) echo "\$ip is unreachable" ;;
              esac
            done
            echo \$count >> {$this->log}
            SH);
        chmod($this->fping, 0755);
        LibrenmsConfig::set('fping', $this->fping);
    }

    protected function tearDown(): void
    {
        unlink($this->fping);
        unlink($this->log);

        parent::tearDown();
    }

    #[TestDox('Pings in batches as the addresses are read, and reads the source lazily')]
    public function testBatches(): void
    {
        $source = (function () {
            for ($i = 1; $i <= 600; $i++) {
                $this->read++;
                yield '10.0.' . intdiv($i, 256) . '.' . ($i % 256);
            }
        })();

        $sweep = new PingSweep;
        $fed = $sweep->feed($source);

        $first = $fed->current();
        $this->assertSame(256, $this->read, 'only the first batch is read before it is yielded');

        $results = [$first => $sweep->isAlive($first)];
        for ($fed->next(); $fed->valid(); $fed->next()) {
            $results[$fed->current()] = $sweep->isAlive($fed->current());
        }
        $sweep->stop();

        $this->assertCount(600, $results);
        $this->assertTrue($results['10.0.0.1']);
        $this->assertFalse($results['10.0.0.2']);
        $this->assertTrue($results['10.0.2.87']);
        $this->assertSame(['256', '256', '88'], file($this->log, FILE_IGNORE_NEW_LINES));
    }

    #[TestDox('An address that was not fed is not alive')]
    public function testUnknownAddress(): void
    {
        $this->assertFalse((new PingSweep)->isAlive('10.0.0.1'));
    }
}
