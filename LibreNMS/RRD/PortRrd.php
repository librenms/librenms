<?php

namespace LibreNMS\RRD;

/**
 * Port rrd file details
 */
final class PortRrd
{
    /** Data sources in the port rrd file that count bytes or packets */
    public const array COUNTERS = [
        'INOCTETS',
        'OUTOCTETS',
        'INERRORS',
        'OUTERRORS',
        'INUCASTPKTS',
        'OUTUCASTPKTS',
        'INNUCASTPKTS',
        'OUTNUCASTPKTS',
        'INDISCARDS',
        'OUTDISCARDS',
        'INUNKNOWNPROTOS',
        'INBROADCASTPKTS',
        'OUTBROADCASTPKTS',
        'INMULTICASTPKTS',
        'OUTMULTICASTPKTS',
    ];

    /**
     * Limits that cap the counters at the port speed (in bytes per second) to drop spikes,
     * none for speeds under 10Mbps
     *
     * @return array<string, array{max: int|float}>
     */
    public static function tuneLimits(int $ifSpeed): array
    {
        $maximum = self::tuneMaximum($ifSpeed);
        if ($maximum === null) {
            return [];
        }

        return array_fill_keys(self::COUNTERS, ['max' => $maximum]);
    }

    public static function tuneMaximum(int $ifSpeed): int|float|null
    {
        return $ifSpeed < 10000000 ? null : $ifSpeed / 8;
    }
}
