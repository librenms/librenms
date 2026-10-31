<?php

namespace LibreNMS\Tests\Unit\TimeSeries;

use App\Data\TimeSeries\MetricIdentity;
use InvalidArgumentException;
use LibreNMS\Tests\TestCase;

class MetricIdentityTest extends TestCase
{
    public function test_it_can_be_instantiated_with_measurement_and_tags(): void
    {
        $identity = new MetricIdentity(1, 'mempool', ['mempool_index' => 10], ['mempool', 'hr', 10]);

        $this->assertEquals(1, $identity->deviceId);
        $this->assertEquals('mempool', $identity->measurement);
        $this->assertEquals(['mempool_index' => 10], $identity->tags);
        $this->assertEquals(['mempool', 'hr', 10], $identity->rrdName);
    }

    public function test_it_throws_exception_for_empty_measurement(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Metric measurement cannot be empty.');

        new MetricIdentity(1, '');
    }

    public function test_it_throws_exception_for_invalid_tag_key(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Metric tag names must be non-empty strings.');

        new MetricIdentity(1, 'mempool', ['' => 'value']);
    }

    public function test_it_throws_exception_for_non_scalar_tag_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Metric tag [key] must be scalar or null.');

        new MetricIdentity(1, 'mempool', $this->untyped(['key' => []]));
    }

    /**
     * Hide the type from static analysis to test the runtime check
     */
    private function untyped(mixed $value): mixed
    {
        return $value;
    }
}
