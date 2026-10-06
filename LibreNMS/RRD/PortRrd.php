<?php

namespace LibreNMS\RRD;

/**
 * Port rrd file details
 */
final class PortRrd
{
    /** Data sources in the port rrd file that count bytes or packets */
    public const COUNTERS = [
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
        if ($ifSpeed < 10000000) {
            return [];
        }

        return array_fill_keys(self::COUNTERS, ['max' => $ifSpeed / 8]);
    }
}
