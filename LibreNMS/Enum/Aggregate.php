<?php

namespace LibreNMS\Enum;

/**
 * Operations that combine several numbers into one.
 * Used by YAML discovery template filters ({{ MIB::column|sum }}) and the wireless sensor aggregator.
 */
enum Aggregate: string
{
    case Sum = 'sum';
    case Avg = 'avg';
    case Min = 'min';
    case Max = 'max';

    /**
     * @param  array<int|float>  $values
     */
    public function apply(array $values): int|float|null
    {
        if (empty($values)) {
            return null;
        }

        return match ($this) {
            self::Sum => array_sum($values),
            self::Avg => array_sum($values) / count($values),
            self::Min => min($values),
            self::Max => max($values),
        };
    }
}
