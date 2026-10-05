<?php

namespace App\Services;

/** One RRD sample: its timestamp and the values of its data sources. */
final class RrdSample
{
    public function __construct(
        public readonly int $timestamp,
        public readonly array $data,
    ) {
    }

    public function ds(): array
    {
        return array_keys($this->data);
    }

    public function get(string $name): int|float|null
    {
        return $this->data[$name] ?? null;
    }
}
