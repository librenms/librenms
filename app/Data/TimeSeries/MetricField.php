<?php

namespace App\Data\TimeSeries;

/**
 * A field of a measurement.
 * For a counter, the limits are those of its rate per second, values outside of them are unknown.
 */
final readonly class MetricField
{
    public function __construct(
        public string $name,
        public FieldKind $kind = FieldKind::Gauge,
        public int|float|null $min = null,
        public int|float|null $max = null,
    ) {
    }

    public static function gauge(string $name, int|float|null $min = null, int|float|null $max = null): self
    {
        return new self($name, FieldKind::Gauge, $min, $max);
    }

    public static function derive(string $name, int|float|null $min = null, int|float|null $max = null): self
    {
        return new self($name, FieldKind::Derive, $min, $max);
    }

    public static function wrapping(string $name, int|float|null $min = null, int|float|null $max = null): self
    {
        return new self($name, FieldKind::Wrapping, $min, $max);
    }

    public function isCounter(): bool
    {
        return $this->kind->isCounter();
    }
}
