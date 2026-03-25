<?php

namespace LibreNMS\Util;

final class DiskIoFilter
{
    public static function normalizeSelection(?string $view, ?string $subtype): array
    {
        if (! in_array((string) $view, ['physical', 'logical', 'all'], true)) {
            $view = 'physical';
        }

        if ($view === 'all') {
            return ['view' => 'all', 'subtype' => 'all'];
        }

        if (! in_array((string) $subtype, self::subtypesFor($view), true)) {
            $subtype = 'all';
        }

        return ['view' => $view, 'subtype' => $subtype];
    }

    public static function subtypesFor(string $view): array
    {
        return match ($view) {
            'physical' => ['all', 'sd_family', 'nvme', 'mmcblk', 'other'],
            'logical' => ['all', 'partitions', 'dm', 'md', 'loop', 'other'],
            default => ['all'],
        };
    }

    public static function classify(string $diskName): array
    {
        if (preg_match('/^dm-\d+$/i', $diskName)) {
            return ['view' => 'logical', 'subtype' => 'dm'];
        }

        if (preg_match('/^loop\d+$/i', $diskName)) {
            return ['view' => 'logical', 'subtype' => 'loop'];
        }

        if (preg_match('/^md\d+$/i', $diskName)) {
            return ['view' => 'logical', 'subtype' => 'md'];
        }

        if (preg_match('/^(sd[a-z]+\d+|hd[a-z]+\d+|vd[a-z]+\d+|xvd[a-z]+\d+)$/i', $diskName)
            || preg_match('/^nvme\d+n\d+p\d+$/i', $diskName)
            || preg_match('/^mmcblk\d+p\d+$/i', $diskName)
            || preg_match('/^md\d+p\d+$/i', $diskName)) {
            return ['view' => 'logical', 'subtype' => 'partitions'];
        }

        if (preg_match('/^(sd[a-z]+|hd[a-z]+|vd[a-z]+|xvd[a-z]+)$/i', $diskName)) {
            return ['view' => 'physical', 'subtype' => 'sd_family'];
        }

        if (preg_match('/^nvme\d+n\d+$/i', $diskName)) {
            return ['view' => 'physical', 'subtype' => 'nvme'];
        }

        if (preg_match('/^mmcblk\d+$/i', $diskName)) {
            return ['view' => 'physical', 'subtype' => 'mmcblk'];
        }

        return ['view' => 'physical', 'subtype' => 'other'];
    }

    public static function matches(array $diskType, string $selectedView, string $selectedSubtype): bool
    {
        if ($selectedView !== 'all' && $diskType['view'] !== $selectedView) {
            return false;
        }

        return $selectedView === 'all' || $selectedSubtype === 'all' || $diskType['subtype'] === $selectedSubtype;
    }
}
