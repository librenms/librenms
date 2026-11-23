<?php

namespace App\Data\Graphing;

/**
 * What a series or reference line shows, the renderer picks its colors for the site style.
 * Drawn series of the same role get different shades where the role has several.
 */
enum SeriesRole
{
    // traffic, or reads and writes
    case In;
    case Out;
    case PacketsIn;
    case PacketsOut;
    case Error;
    case Discard;
    // the measured value, in the color of the legend text
    case Value;
    // what stays in the background, such as free space or the range of a value
    case Muted;
    case Used;
    case Total;
    case Loss;
    // used capacity, choose with usage()
    case UsageLow;
    case UsageModerate;
    case UsageHigh;
    case UsageVeryHigh;
    case UsageCritical;
    // threshold lines
    case LowLimit;
    case LowWarning;
    case HighWarning;
    case HighLimit;
    case PortSpeed;

    /**
     * The usage role for a used percentage, critical above the warning percentage
     */
    public static function usage(float $percent, ?float $warn = null): self
    {
        if ($percent > round($warn ?? 75)) {
            return self::UsageCritical;
        }

        if ($percent > 75) {
            return self::UsageVeryHigh;
        }

        if ($percent > 50) {
            return self::UsageHigh;
        }

        return $percent > 25 ? self::UsageModerate : self::UsageLow;
    }
}
