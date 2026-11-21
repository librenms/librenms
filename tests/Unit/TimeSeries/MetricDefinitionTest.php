<?php

namespace LibreNMS\Tests\Unit\TimeSeries;

use App\Data\TimeSeries\FieldKind;
use App\Data\TimeSeries\MetricDefinition;
use App\Data\TimeSeries\MetricField;
use LibreNMS\Tests\TestCase;

final class MetricDefinitionTest extends TestCase
{
    public function testDerivesAndCountersAreCounters(): void
    {
        $this->assertFalse(FieldKind::Gauge->isCounter());
        $this->assertTrue(FieldKind::Derive->isCounter());
        $this->assertTrue(FieldKind::Counter->isCounter());

        $this->assertFalse(MetricField::gauge('usage')->isCounter());
        $this->assertTrue(MetricField::derive('in')->isCounter());
        $this->assertTrue(MetricField::counter('in')->isCounter());
    }

    public function testFieldsKeepTheirLimits(): void
    {
        $field = MetricField::derive('in', 0, 100);

        $this->assertSame('in', $field->name);
        $this->assertSame(FieldKind::Derive, $field->kind);
        $this->assertSame(0, $field->min);
        $this->assertSame(100, $field->max);

        $unbounded = MetricField::gauge('value');
        $this->assertNull($unbounded->min);
        $this->assertNull($unbounded->max);
    }

    public function testValuesOutsideTheLimitsAreRejected(): void
    {
        $field = MetricField::gauge('usage', -273, 1000);

        $this->assertTrue($field->accepts(-273));
        $this->assertTrue($field->accepts(50.5));
        $this->assertTrue($field->accepts(1000));
        $this->assertFalse($field->accepts(-274));
        $this->assertFalse($field->accepts(1000.1));

        $this->assertTrue(MetricField::gauge('value')->accepts(PHP_INT_MAX));
        $this->assertFalse(MetricField::derive('in', 0)->accepts(-1));
    }

    public function testFieldsAreFoundByNameInTheirOrder(): void
    {
        $definition = new MetricDefinition(
            MetricField::derive('in', 0),
            MetricField::gauge('speed'),
            MetricField::counter('out', 0),
        );

        $this->assertSame(['in', 'speed', 'out'], array_keys($definition->fields));
        $this->assertSame('speed', $definition->field('speed')?->name);
        $this->assertNull($definition->field('missing'));
        $this->assertSame(['in', 'out'], $definition->counters());
    }

    public function testAMetricWithoutCounters(): void
    {
        $this->assertSame([], new MetricDefinition(MetricField::gauge('usage'))->counters());
        $this->assertSame([], new MetricDefinition()->fields);
    }
}
