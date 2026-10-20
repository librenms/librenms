<?php

namespace LibreNMS\Tests\Unit\Scan;

use LibreNMS\Scan\FiberPool;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('FiberPool')]
final class FiberPoolTest extends TestCase
{
    #[TestDox('Runs every task, at most the given number at a time')]
    public function testConcurrency(): void
    {
        $running = 0;
        $maxRunning = 0;
        $task = function (int $item) use (&$running, &$maxRunning): int {
            $running++;
            $maxRunning = max($maxRunning, $running);
            $polls = 0;
            FiberPool::waitUntil(function () use (&$polls, $item): bool {
                return ++$polls > $item % 3; // finish in a different order than started
            });
            $running--;

            return $item * 10;
        };

        $results = [];
        (new FiberPool)->run(range(1, 10), $task, 3, function (int $item, int $result) use (&$results): void {
            $results[$item] = $result;
        });

        ksort($results);
        $this->assertSame(array_combine(range(1, 10), range(10, 100, 10)), $results);
        $this->assertSame(3, $maxRunning);
    }

    #[TestDox('waitUntil works outside a fiber')]
    public function testWaitUntilWithoutFiber(): void
    {
        $polls = 0;
        FiberPool::waitUntil(function () use (&$polls): bool {
            return ++$polls === 3;
        });

        $this->assertSame(3, $polls);
    }
}
