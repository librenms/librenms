<?php

namespace App\Data\TimeSeries;

/**
 * What the values of a metric field are.
 * Counters are stored as polled by datastores that keep raw values, readers turn them into a rate per second.
 */
enum FieldKind
{
    // a value as it is at the time of polling
    case Gauge;
    // a counter that only grows, a value lower than the last one is a reset, not a wrap
    case Derive;
    // a counter that wraps at 32 or 64 bits
    case Counter;

    public function isCounter(): bool
    {
        return $this !== self::Gauge;
    }
}
