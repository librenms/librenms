<?php

namespace LibreNMS\Tests\Unit\Enum;

use LibreNMS\Enum\Aggregate;
use LibreNMS\Tests\TestCase;

final class AggregateTest extends TestCase
{
    public function testApply(): void
    {
        $values = [10, 1, 5];

        $this->assertSame(16, Aggregate::Sum->apply($values));
        $this->assertEqualsWithDelta(5.333, Aggregate::Avg->apply($values), 0.001);
        $this->assertSame(1, Aggregate::Min->apply($values));
        $this->assertSame(10, Aggregate::Max->apply($values));
        $this->assertNull(Aggregate::Sum->apply([]));
    }
}
