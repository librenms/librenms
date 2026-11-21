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

    public static function counter(string $name, int|float|null $min = null, int|float|null $max = null): self
    {
        return new self($name, FieldKind::Counter, $min, $max);
    }

    public function isCounter(): bool
    {
        return $this->kind->isCounter();
    }

    /**
     * Whether a value (the rate of a counter) is within the limits of this field
     */
    public function accepts(int|float $value): bool
    {
        return ($this->min === null || $value >= $this->min) && ($this->max === null || $value <= $this->max);
    }
}
