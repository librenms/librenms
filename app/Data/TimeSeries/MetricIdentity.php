<?php

namespace App\Data\TimeSeries;

use InvalidArgumentException;

final readonly class MetricIdentity
{
    /**
     * @param  array<string, scalar|null>  $labels
     */
    public function __construct(
        public string $name,
        public array $labels = [],
    )
    {
        if ($name === '') {
            throw new InvalidArgumentException('Metric name cannot be empty.');
        }

        foreach ($labels as $key => $value) {
            if (! is_string($key) || $key === '') {
                throw new InvalidArgumentException(
                    'Metric label names must be non-empty strings.',
                );
            }

            if (! is_scalar($value) && $value !== null) {
                throw new InvalidArgumentException(
                    "Metric label [$key] must be scalar or null.",
                );
            }
        }
    }

    /**
     * @return array<string, scalar|null>
     */
    public function normalizedLabels(): array
    {
        $labels = $this->labels;
        ksort($labels, SORT_STRING);

        return $labels;
    }
}
