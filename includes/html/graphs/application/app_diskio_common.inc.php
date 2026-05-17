<?php

/**
 * Build an rrd_list from ucd_diskio RRDs for the given device candidate sets.
 *
 * Shared by any application whose member devices are tracked via ucd_diskio.
 *
 * @param  array              $device
 * @param  list<list<string>> $deviceCandidateSets  Ordered candidate diskio_descr
 *                                                   strings per device. The first
 *                                                   candidate that matches a known
 *                                                   ucd_diskio entry is used.
 *                                                   Callers should pre-compute
 *                                                   /dev/-stripped and basename
 *                                                   variants.
 * @param  string             $context              Included in exception messages.
 * @return list<array{filename: string, descr: string}>
 */
function app_diskio_build_rrd_list(array $device, array $deviceCandidateSets, string $context = ''): array
{
    $rows = dbFetchRows(
        'SELECT diskio_descr FROM ucd_diskio WHERE device_id = ? ORDER BY diskio_descr',
        [$device['device_id']]
    );
    $known = [];
    foreach ($rows as $row) {
        $d = trim((string) ($row['diskio_descr'] ?? ''));
        if ($d !== '') {
            $known[$d] = true;
        }
    }

    $matched = [];
    foreach ($deviceCandidateSets as $candidates) {
        foreach ($candidates as $candidate) {
            if (isset($known[$candidate])) {
                $matched[] = $candidate;
                break;
            }
        }
    }

    $matched = array_values(array_unique($matched));
    $suffix = $context !== '' ? " for $context" : '';
    if ($matched === []) {
        throw new LibreNMS\Exceptions\RrdGraphException('No matching diskio entries' . $suffix);
    }

    $rrd_list = [];
    foreach ($matched as $descr) {
        $filename = App\Facades\Rrd::name($device['hostname'], ['ucd_diskio', $descr]);
        if (App\Facades\Rrd::checkRrdExists($filename)) {
            $rrd_list[] = ['filename' => $filename, 'descr' => $descr];
        }
    }

    if ($rrd_list === []) {
        throw new LibreNMS\Exceptions\RrdGraphException('No matching diskio RRDs' . $suffix);
    }

    return $rrd_list;
}
