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
        return empty($values) ? null : collect($values)->{$this->value}();
    }
}
