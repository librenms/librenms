@php
// ---------------------------------------------------------------------------
// Graph / table definitions
// ---------------------------------------------------------------------------
$legacyGraphs = [
    'level'          => 'RAID Level',
    'size'           => 'RAID Size',
    'disc_count'     => 'Disk Count',
    'hotspare_count' => 'Hotspare Count',
    'degraded'       => 'Degraded',
    'sync_speed'     => 'Sync Speed',
    'sync_completed' => 'Sync Completed',
];

$arrayGraphs = [
    'disk_counts' => ['type' => 'mdadm_app',        'metric' => 'disk_counts', 'title' => 'Disk Counts (disks)'],
    'mismatch'    => ['type' => 'mdadm_mismatch',                               'title' => 'Mismatch'],
    'sync_bps'    => ['type' => 'mdadm_app',        'metric' => 'sync_bps',    'title' => 'Sync Speed (B/s)'],
    'sync_pct'    => ['type' => 'mdadm_app',        'metric' => 'sync_pct',    'title' => 'Sync Progress (%)'],
    'diskio_ops'  => ['type' => 'mdadm_diskio_ops',                             'title' => 'Disk I/O Ops'],
    'diskio_bits' => ['type' => 'mdadm_diskio_bits',                            'title' => 'Disk I/O Bytes'],
];

$statusFields = [
    'Array Name'         => ['key' => 'array_name',         'tooltip' => 'Human-readable name assigned with --name when creating or assembling the array.'],
    'RAID Level'         => ['key' => 'raid_level',         'tooltip' => 'The RAID level (e.g. raid0, raid1, raid5, raid6, raid10, linear).'],
    'State'              => ['key' => 'state',              'tooltip' => 'Current array state: clean, active, degraded, write-pending, suspended, readonly, read-auto.'],
    'UUID'               => ['key' => 'uuid',               'tooltip' => 'Universally unique identifier for this array, shared across all member devices.'],
    'Metadata'           => ['key' => 'metadata_version',   'tooltip' => 'Superblock version. 0.90 legacy; 1.0/1.1/1.2 modern (supports arrays > 2TiB).'],
    'Consistency Policy' => ['key' => 'consistency_policy', 'tooltip' => 'How the array maintains consistency: none, resync, bitmap, journal, or ppl.'],
];

$diskCountFields = [
    'Total Disks' => ['key' => 'raid_disks',     'tooltip' => 'Number of member devices configured (raid_disks). Does not include spares.'],
    'Active'      => ['key' => 'active_devices', 'tooltip' => 'Number of devices currently active and contributing to the array.'],
    'Spare'       => ['key' => 'spare_devices',  'tooltip' => 'Number of hot-spare devices standing by.'],
    'Failed'      => ['key' => 'failed_devices', 'tooltip' => 'Number of devices marked faulty and removed from the active set.'],
];

$deviceHeaders = [
    'Path'      => 'Block device path (e.g. sda5).',
    'Role'      => 'Role this device plays in the array: active member, spare, journal, or replacement.',
    'Health'    => 'Device state flags from the kernel.',
    'Slot'      => 'Slot (raid_disk) this device occupies. -1 means spare.',
    'Errors'    => 'Cumulative count of read errors on this device.',
    'Events'    => 'Superblock event counter. All healthy members of an array share the same value; a lagging counter indicates a stale device.',
    'Bad Blocks' => 'Number of bad blocks recorded for this device in the bad-block log. A parenthesised value is the count not yet acknowledged.',
    'Recovery'  => 'Offset at which recovery of this member is currently in progress. Blank when the device is fully in sync.',
    'Offset'    => 'Offset into the device where the array data starts (data_offset), leaving room for the superblock and write-intent bitmap.',
    'PPL'       => 'Partial Parity Log location and size on this member (raid5 ppl consistency policy). Blank when not in use.',
    'Model'     => '',
    'Serial'    => '',
    'Size'      => '',
];

// Per-column client-side sort behaviour for the drives table (keys match $deviceHeaders).
$deviceSortTypes = [
    'Path'       => 'string',
    'Role'       => 'string',
    'Health'     => 'numeric',
    'Slot'       => 'numeric',
    'Errors'     => 'numeric',
    'Events'     => 'numeric',
    'Bad Blocks' => 'numeric',
    'Recovery'   => 'numeric',
    'Offset'     => 'numeric',
    'PPL'        => 'numeric',
    'Model'      => 'string',
    'Serial'     => 'string',
    'Size'       => 'numeric',
];

// ---------------------------------------------------------------------------
// HTML helpers (closures)
// ---------------------------------------------------------------------------
$tableRow = static function (string $label, string $value, string $tooltip = ''): string {
    $labelEsc = htmlspecialchars($label);
    if ($tooltip !== '') {
        $ttAttr = ' title="' . htmlspecialchars($tooltip) . '"';
        $labelHtml = "<abbr style=\"cursor:help;text-decoration:underline dotted\"{$ttAttr}>{$labelEsc}</abbr>";
    } else {
        $labelHtml = $labelEsc;
    }
    return "<tr><td style=\"text-align:right;padding-right:15px;white-space:nowrap\"><strong>{$labelHtml}</strong></td><td>{$value}</td></tr>\n";
};

$panelStart = static function (string $title, string $badge = ''): void {
    $badgeHtml = $badge !== '' ? "<span class=\"pull-right\">{$badge}</span>" : '';
    echo "<div class=\"panel panel-default\"><div class=\"panel-heading\"><h3 class=\"panel-title\">{$title}{$badgeHtml}</h3></div><div class=\"panel-body\">";
};

$panelEnd = static function (): void {
    echo '</div></div>';
};

$linkArray = [
    'page'   => 'device',
    'device' => $data->device['device_id'],
    'tab'    => 'apps',
    'app'    => 'mdadm',
];

// Render a 512-byte sector count as a human-readable size, or a muted dash when unset.
$fmtSectors = static function ($sectors): string {
    if ($sectors === null || $sectors === '') {
        return '<span class="text-muted">-</span>';
    }
    return LibreNMS\Util\Number::formatBi((int) $sectors * 512);
};

// Render a SNMP TruthValue / boolean column as a Yes/No badge, or a muted dash when unset.
$fmtBool = static function ($value, string $yesClass = 'default'): string {
    if ($value === null || $value === '') {
        return '<span class="text-muted">-</span>';
    }
    return (bool) $value
        ? mdadm_badge('Yes', $yesClass)
        : mdadm_badge('No', 'default');
};
@endphp

{{-- Optionbar --}}
@php
    print_optionbar_start();
    $ovLabel = $selectedArray === null
        ? '<span class="pagemenu-selected">Overview</span>'
        : 'Overview';
    echo generate_link($ovLabel, $linkArray);
    if (!empty($data->arrayData)) {
        echo ' | Arrays: ';
        $arrays = array_keys($data->arrayData);
        foreach ($arrays as $i => $MDid) {
            $label = htmlspecialchars($MDid);
            if ($selectedArray === $MDid) {
                $label = "<span class=\"pagemenu-selected\">{$label}</span>";
            }
            echo generate_link($label, $linkArray, ['array' => $MDid]);
            if ($i < count($arrays) - 1) { echo ', '; }
        }
    }
    if (Auth::user()->hasRole('admin')) {
        echo '<span class="pull-right">' . debug_toggle_button('mdadm-debug-panels') . '</span>';
    }
    print_optionbar_end();
@endphp

{{-- Debug panels --}}
@php
    mdadm_debug_render(
        $data->app->app_id,
        $data->allSensors,
        'mdadm',
        (string) ($data->device['hostname'] ?? '')
    );
@endphp

@if($selectedArray === null)
    {{-- ================================================================== --}}
    {{-- Overview                                                            --}}
    {{-- ================================================================== --}}
    @php
        $totalArrays    = (int) ($data->appMetrics['arrays']          ?? count($data->arrayData));
        $totalDevices   = (int) ($data->appMetrics['devices_total']   ?? 0);
        $degradedArrays = (int) ($data->appMetrics['degraded_arrays'] ?? 0);
        $syncingArrays  = (int) ($data->appMetrics['arrays_syncing']  ?? 0);

        $panelStart('Summary');
        echo '<div class="row text-center">';
        foreach ([
            ['value' => $totalArrays,    'label' => 'Arrays',   'class' => ''],
            ['value' => $totalDevices,   'label' => 'Devices',  'class' => ''],
            ['value' => $degradedArrays, 'label' => 'Degraded', 'class' => $degradedArrays > 0 ? 'text-danger' : ''],
            ['value' => $syncingArrays,  'label' => 'Syncing',  'class' => $syncingArrays  > 0 ? 'text-info' : 'text-muted'],
        ] as $item) {
            $cls = $item['class'] !== '' ? ' ' . $item['class'] : '';
            echo "<div class=\"col-sm-3{$cls}\"><h4>{$item['value']}</h4><small>{$item['label']}</small></div>";
        }
        echo '</div>';
        $panelEnd();
    @endphp

    @if(!empty($data->arrayData))
        <table id="mdadm-arrays-table"
               class="table table-condensed table-responsive table-striped"
               data-url="{{ route('table.mdadm-array') }}"
               data-app-id="{{ $app->app_id }}">
            <thead>
            <tr>
                <th data-column-id="array_name"     data-sortable="true">Array Name</th>
                <th data-column-id="md_id"          data-sortable="true">MDid</th>
                <th data-column-id="level"          data-sortable="true">Level</th>
                <th data-column-id="state"          data-sortable="true">State</th>
                <th data-column-id="sync_action"    data-sortable="true">Operation</th>
                <th data-column-id="raid_disks"     data-sortable="true" data-type="numeric">Disks</th>
                <th data-column-id="active_devices" data-sortable="true" data-type="numeric">Active</th>
                <th data-column-id="spare_devices"  data-sortable="true" data-type="numeric">Spare</th>
                <th data-column-id="failed_devices" data-sortable="true" data-type="numeric">Failed</th>
                <th data-column-id="size"           data-sortable="false">Size</th>
                <th data-column-id="mismatch_cnt"   data-sortable="true" data-type="numeric">Mismatches</th>
            </tr>
            </thead>
        </table>
        <script>
            $("#mdadm-arrays-table").bootgrid({
                ajax: true,
                post: function() { return { app_id: {{ $app->app_id }} }; },
            });
        </script>

        {{-- Per-array graph panels (v3) or legacy graphs --}}
        @if($data->isLegacy)
            @php
                foreach ($legacyGraphs as $metric => $text) {
                    $graph_array = [
                        'height' => '100', 'width' => '215',
                        'to'     => time(),
                        'id'     => $data->app->app_id,
                        'type'   => 'mdadm_legacy',
                        'metric' => $metric,
                        'legend' => 'no',
                    ];
                    echo "<div class=\"panel panel-default\"><div class=\"panel-heading\"><h3 class=\"panel-title\">{$text}</h3></div><div class=\"panel-body\">";
                    include 'includes/html/print-graphrow.inc.php';
                    echo '</div></div>';
                }
            @endphp
        @else
            @foreach($data->arrayNames() as $MDid)
                @php
                    $arrData = $data->array($MDid);
                    $hEntry  = $arrData['mdadm_array_health_status'] ?? [];
                    $hBadge  = mdadm_badge($hEntry['label'] ?? 'Unknown', $hEntry['class'] ?? 'default', $hEntry['info'] ?? '');
                    $arrUrl  = LibreNMS\Util\Url::generate($linkArray + ['array' => $MDid]);
                    $MDidEsc = htmlspecialchars($MDid);

                    $panelStart("<a href=\"{$arrUrl}\">{$MDidEsc}</a>", $hBadge);
                    echo '<div class="row">';
                    foreach ($arrayGraphs as $spec) {
                        $graphArray = [
                            'height' => '80', 'width' => '180',
                            'type'   => $spec['type'],
                            'id'     => $data->app->app_id,
                            'array'  => $MDid,
                            'from'   => App\Facades\LibrenmsConfig::get('time.day'),
                            'to'     => App\Facades\LibrenmsConfig::get('time.now'),
                            'legend' => 'no',
                        ];
                        if (isset($spec['metric'])) { $graphArray['metric'] = $spec['metric']; }
                        $titleEsc = htmlspecialchars($spec['title']);
                        $graphTag = LibreNMS\Util\Url::lazyGraphTag($graphArray);
                        echo "<div class=\"pull-left\" style=\"margin-right:8px;margin-bottom:8px\"><div class=\"text-muted\" style=\"font-size:11px;margin-bottom:4px\">{$titleEsc}</div><a href=\"{$arrUrl}\">{$graphTag}</a></div>";
                    }
                    echo '</div>';
                    $panelEnd();
                @endphp
            @endforeach
        @endif
    @endif

@else
    {{-- ================================================================== --}}
    {{-- Per-array view                                                      --}}
    {{-- ================================================================== --}}
    @php
        $array     = (string) $selectedArray;
        $arrData  = $data->array($array);
        $meta     = $data->arraysMeta[$array] ?? [];
        $sync     = $data->syncDataForArray($array);
        $hEntry   = $arrData['mdadm_array_health_status']    ?? [];
        $opEntry  = $arrData['mdadm_array_operation_status'] ?? [];
        $hBadge   = mdadm_badge($hEntry['label']  ?? 'Unknown', $hEntry['class']  ?? 'default', $hEntry['info']  ?? '');
        $opBadge  = mdadm_badge($opEntry['label'] ?? 'Unknown', $opEntry['class'] ?? 'default', $opEntry['info'] ?? '');
    @endphp

    <div class="row">

        {{-- Status panel --}}
        <div class="col-md-6" style="display:inline-block;float:none;width:auto;vertical-align:top">
            @php
                $sizeStr     = isset($meta['size_bytes']) && $meta['size_bytes'] > 0
                    ? LibreNMS\Util\Number::formatBi($meta['size_bytes'])
                    : '-';
                $mismatchRaw = $arrData['mdadm_array_mismatch']['val'] ?? '-';
                $mismatchVal = is_numeric($mismatchRaw) ? (int) $mismatchRaw : null;
                $chunkSize   = (int) ($meta['chunk_size'] ?? 0);

                $panelStart(htmlspecialchars($meta['array_name'] ) . ' Status', $hBadge);
                echo '<table class="table table-condensed table-hover" style="width:auto">';
                foreach ($statusFields as $label => $spec) {
                    echo $tableRow($label, htmlspecialchars((string) ($meta[$spec['key']] ?? '-')), $spec['tooltip']);
                }
                echo $tableRow('Array Size', $sizeStr,
                    'Total usable size of the array (after RAID overhead). For raid1 this is one member size; for raid5 it is (N-1) × member size.');
                echo $tableRow('Chunk Size', $chunkSize > 0 ? LibreNMS\Util\Number::formatBi($chunkSize) : '-',
                    'Stripe unit for raid0/4/5/6/10. Larger chunks improve sequential I/O; smaller chunks improve random I/O parallelism.');
                echo $tableRow(
                    'Mismatches',
                    $mismatchVal !== null
                        ? '<span' . ($mismatchVal > 0 ? ' class="text-warning"' : '') . '>' . $mismatchVal . '</span>'
                        : '-',
                    'Number of sectors found inconsistent during the last check or repair operation.'
                );

                if (($meta['layout_label'] ?? null) !== null && $meta['layout_label'] !== '') {
                    echo $tableRow('Layout', htmlspecialchars((string) $meta['layout_label']),
                        'Parity/copy placement policy. RAID-4/5/6 use left/right symmetric/asymmetric; RAID-10 uses near/far/offset copies.');
                }

                if (array_key_exists('is_mounted', $meta) && $meta['is_mounted'] !== null) {
                    $mountPoints = trim((string) ($meta['mount_points'] ?? ''));
                    $mountedVal  = $fmtBool($meta['is_mounted']);
                    if ($mountPoints !== '') {
                        $mountedVal .= ' <span class="text-muted">' . htmlspecialchars($mountPoints) . '</span>';
                    }
                    echo $tableRow('Mounted', $mountedVal,
                        'Whether a filesystem on this array is currently mounted, and at which mount point(s).');
                }

                if (array_key_exists('is_swap', $meta) && $meta['is_swap'] !== null) {
                    echo $tableRow('Swap', $fmtBool($meta['is_swap']),
                        'Whether this array is currently in use as a swap device.');
                }

                if (($meta['stripe_cache_size'] ?? null) !== null) {
                    $scSize   = (int) $meta['stripe_cache_size'];
                    $scActive = $meta['stripe_cache_active'] ?? null;
                    $scStr    = number_format($scSize) . ' pages';
                    if ($scActive !== null) {
                        $scStr .= ' <span class="text-muted">(' . number_format((int) $scActive) . ' active)</span>';
                    }
                    echo $tableRow('Stripe Cache', $scStr,
                        'Size of the stripe cache in pages per device (raid5/6). Larger caches can improve write performance at the cost of memory.');
                }

                if (($meta['journal_mode'] ?? null) !== null && $meta['journal_mode'] !== '') {
                    echo $tableRow('Journal Mode', htmlspecialchars((string) $meta['journal_mode']),
                        'Write-journal mode for arrays with a journal device: write-through or write-back.');
                }

                echo '</table>';
                $panelEnd();
            @endphp
        </div>

        {{-- Sync panel --}}
        <div class="col-md-3" style="display:inline-block;float:none;width:auto;vertical-align:top">
            @php
                $isSyncing  = $sync['is_syncing'];
                $speedBps   = (int) ($sync['speed_bps']     ?? 0);
                $speedMin   = (int) ($sync['speed_min_bps'] ?? 0);
                $speedMax   = (int) ($sync['speed_max_bps'] ?? 0);
                $syncDone   = (int) ($sync['done_bytes']    ?? 0);
                $syncTotal  = (int) ($sync['total_bytes']   ?? 0);
                $syncPct    = (float) ($sync['completed_pct'] ?? 0);
                $lastAction = (string) ($sync['last_action'] ?? '');

                $speedStr   = ($isSyncing && $speedBps > 0) ? LibreNMS\Util\Number::formatBi($speedBps, suffix: 'B/s') : '-';
                $minStr     = $speedMin > 0 ? LibreNMS\Util\Number::formatBi($speedMin, suffix: 'B/s') : '<span class="text-muted">-</span>';
                $maxStr     = $speedMax > 0 ? LibreNMS\Util\Number::formatBi($speedMax, suffix: 'B/s') : '<span class="text-muted">-</span>';
                $barPct     = $syncTotal > 0 ? max(0, min(100, $syncPct)) : 0;
                $barPctFmt  = number_format($barPct, 1);
                $doneStr    = LibreNMS\Util\Number::formatBi(max(0, $syncDone));
                $totalStr   = $syncTotal > 0 ? LibreNMS\Util\Number::formatBi($syncTotal) : '-';
                $lastOpStr  = $lastAction !== '' ? htmlspecialchars(ucfirst($lastAction)) : '<span class="text-muted">-</span>';

                $syncRows = $tableRow('Last operation', $lastOpStr,
                    'The sync action most recently run: check, resync, recover, or repair.')
                    . $tableRow('Speed limits', "{$minStr} / {$maxStr}",
                        'Minimum / maximum sync speed in bytes/sec. Controlled by /proc/sys/dev/raid/speed_limit_min and speed_limit_max.')
                    . $tableRow('Speed', $speedStr, 'Current sync throughput in bytes/sec.')
                    . $tableRow('Progress',
                        "<div style=\"min-width:200px\"><div class=\"progress\" style=\"margin-bottom:4px\"><div class=\"progress-bar progress-bar-info\" style=\"width:{$barPct}%;color:#111\">{$barPctFmt}%</div></div><small class=\"text-muted\">{$doneStr} / {$totalStr}</small></div>",
                        'Fraction of the current sync operation completed.');

                if (($meta['resync_start_sectors'] ?? null) !== null) {
                    $syncRows .= $tableRow('Resync start', $fmtSectors($meta['resync_start_sectors']),
                        'Offset at which the next resync will start. Set when a resync is paused; blank after a clean shutdown.');
                }
                if (($meta['reshape_position_sectors'] ?? null) !== null) {
                    $syncRows .= $tableRow('Reshape position', $fmtSectors($meta['reshape_position_sectors']),
                        'Progress point of an in-flight reshape (e.g. changing level, chunk size, or device count).');
                }
                $checkMin = $sync['min_sectors'] ?? null;
                $checkMax = $sync['max_sectors'] ?? null;
                if ($checkMin !== null || $checkMax !== null) {
                    $syncRows .= $tableRow('Check range',
                        $fmtSectors($checkMin) . ' / ' . $fmtSectors($checkMax),
                        'Sector window the current check/resync is restricted to (sync_min / sync_max). Max blank means the whole array.');
                }

                $panelStart('Sync', $opBadge);
                echo "<table class=\"table table-condensed table-hover\" style=\"width:auto\">{$syncRows}</table>";
                $panelEnd();
            @endphp
        </div>

        {{-- Disk counts panel --}}
        <div class="col-md-3" style="display:inline-block;float:none;width:auto;vertical-align:top">
            @php
                $diskRows = '';
                foreach ($diskCountFields as $label => $spec) {
                    $diskRows .= $tableRow($label, (string) (int) ($meta[$spec['key']] ?? 0), $spec['tooltip']);
                }
                $degradedVal = $meta['degraded'] ?? null;
                if ($degradedVal !== null) {
                    $dgClass  = (int) $degradedVal > 0 ? ' class="text-danger"' : '';
                    $diskRows .= $tableRow('Degraded', "<span{$dgClass}>" . (int) $degradedVal . '</span>',
                        'Count of missing active members. Data is still accessible but fault tolerance is reduced.');
                }
                $panelStart('Disk Counts');
                echo "<table class=\"table table-condensed table-hover\" style=\"width:auto\">{$diskRows}</table>";
                $panelEnd();
            @endphp
        </div>

        {{-- Bitmap panel --}}
        @php
            $bitmapType = trim((string) ($meta['bitmap_type'] ?? ''));
        @endphp
        @if($bitmapType !== '' && strtolower($bitmapType) !== 'none')
            <div class="col-md-3" style="display:inline-block;float:none;width:auto;vertical-align:top">
                @php
                    $bmRows  = $tableRow('Type', htmlspecialchars($bitmapType),
                        'Write-intent bitmap type: internal (in the array metadata), external (a separate file), or lockless.');
                    if (($meta['bitmap_location'] ?? null) !== null && $meta['bitmap_location'] !== '') {
                        $bmRows .= $tableRow('Location', htmlspecialchars((string) $meta['bitmap_location']),
                            'Where the bitmap lives, e.g. an offset relative to the superblock or an external file path.');
                    }
                    if (($meta['bitmap_chunksize'] ?? null) !== null) {
                        $bmRows .= $tableRow('Chunk size', $fmtSectors(((int) $meta['bitmap_chunksize']) / 512),
                            'Amount of array space each bitmap bit covers. Larger chunks mean a smaller bitmap but coarser resync granularity.');
                    }
                    if (($meta['bitmap_metadata'] ?? null) !== null && $meta['bitmap_metadata'] !== '') {
                        $bmRows .= $tableRow('Metadata', htmlspecialchars((string) $meta['bitmap_metadata']),
                            'Bitmap metadata format / version.');
                    }
                    if (($meta['bitmap_time_base'] ?? null) !== null) {
                        $bmRows .= $tableRow('Time base', number_format((int) $meta['bitmap_time_base']) . ' s',
                            'Seconds of idle time before a bitmap region is marked clean and flushed.');
                    }
                    if (($meta['bitmap_backlog'] ?? null) !== null || ($meta['bitmap_max_backlog'] ?? null) !== null) {
                        $bmBacklog    = $meta['bitmap_backlog'] ?? null;
                        $bmMaxBacklog = $meta['bitmap_max_backlog'] ?? null;
                        $bmRows .= $tableRow('Backlog',
                            ($bmBacklog === null ? '-' : number_format((int) $bmBacklog))
                                . ' / ' . ($bmMaxBacklog === null ? '-' : number_format((int) $bmMaxBacklog)),
                            'Outstanding / maximum write-behind requests allowed for write-mostly members.');
                    }
                    if (array_key_exists('bitmap_can_clear', $meta) && $meta['bitmap_can_clear'] !== null) {
                        $bmRows .= $tableRow('Can clear', $fmtBool($meta['bitmap_can_clear']),
                            'Whether the array is currently allowed to clear bits in the write-intent bitmap.');
                    }

                    $panelStart('Bitmap');
                    echo "<table class=\"table table-condensed table-hover\" style=\"width:auto\">{$bmRows}</table>";
                    $panelEnd();
                @endphp
            </div>
        @endif

    </div>{{-- /.row --}}

    {{-- Drives table --}}
    @php
        $sensorDevices = $arrData['devices'] ?? [];
        $metaDevices   = (array) ($data->arraysDevices[$array] ?? []);

        // Default ordering: by slot ascending, members with no slot (-1 / null) last.
        uasort($metaDevices, static function ($a, $b) {
            $sa = isset($a['slot']) && is_numeric($a['slot']) ? (int) $a['slot'] : PHP_INT_MAX;
            $sb = isset($b['slot']) && is_numeric($b['slot']) ? (int) $b['slot'] : PHP_INT_MAX;
            if ($sa < 0) { $sa = PHP_INT_MAX - 1; }
            if ($sb < 0) { $sb = PHP_INT_MAX - 1; }
            return $sa <=> $sb;
        });

        $slotColIndex = array_search('Slot', array_keys($deviceHeaders), true);
    @endphp
    @if(!empty($metaDevices))
        @php
            $panelStart('Drives');
            $ths = '';
            foreach ($deviceHeaders as $h => $tip) {
                $tipAttr  = $tip !== '' ? ' title="' . htmlspecialchars($tip) . '"' : '';
                $sortType = $deviceSortTypes[$h] ?? 'string';
                $ths .= "<th{$tipAttr} data-sort-type=\"{$sortType}\" style=\"cursor:pointer;white-space:nowrap\">"
                    . htmlspecialchars($h) . '</th>';
            }
            echo '<table id="mdadm-drives-table" class="table table-condensed table-hover mdadm-sortable"'
                . ' data-default-sort="' . (int) $slotColIndex . '" data-default-sort-type="numeric">'
                . "<thead><tr>{$ths}</tr></thead><tbody>";
        @endphp

        @foreach($metaDevices as $devKey => $metaDev)
            @php
                $dev   = is_array($sensorDevices[$devKey] ?? null) ? $sensorDevices[$devKey] : [];
                $path  = (string) ($metaDev['path'] ?? $devKey);

                $dhEntry  = $dev['mdadm_device_health_status'] ?? [];
                $errRaw   = $dev['mdadm_device_error']['val'] ?? $dev['mdadm_device_errors']['val'] ?? '-';
                $errVal   = is_numeric($errRaw) ? (int) $errRaw : null;
                $sizeBytes = (int) ($metaDev['size_bytes'] ?? 0);

                $eventsVal = $metaDev['events'] ?? null;

                $badRaw   = $metaDev['bad_block_count'] ?? null;
                $unackRaw = $metaDev['unack_bad_block_count'] ?? null;
                if ($badRaw === null) {
                    $badStr = '<span class="text-muted">-</span>';
                } else {
                    $badCount = (int) $badRaw;
                    $badStr   = $badCount > 0 ? '<span class="text-warning">' . $badCount . '</span>' : '0';
                    if ($unackRaw !== null && (int) $unackRaw > 0) {
                        $badStr .= ' <span class="text-danger">(' . (int) $unackRaw . ')</span>';
                    }
                }

                $pplSector = $metaDev['ppl_sector'] ?? null;
                $pplSize   = $metaDev['ppl_size_sectors'] ?? null;
                if ($pplSector === null && $pplSize === null) {
                    $pplStr = '<span class="text-muted">-</span>';
                } else {
                    $pplStr = $fmtSectors($pplSector);
                    if ($pplSize !== null) {
                        $pplStr .= ' <span class="text-muted">(' . $fmtSectors($pplSize) . ')</span>';
                    }
                }

                $cells = [
                    htmlspecialchars($path),
                    htmlspecialchars((string) ($metaDev['device_role']     ?? '-')),
                    mdadm_badge($dhEntry['label'] ?? 'Unknown', $dhEntry['class'] ?? 'default', $dhEntry['info'] ?? ''),
                    htmlspecialchars((string) ($metaDev['slot']            ?? '-')),
                    $errVal !== null ? ($errVal > 0 ? '<span class="text-warning">' . $errVal . '</span>' : (string) $errVal) : '-',
                    $eventsVal !== null ? number_format((int) $eventsVal) : '<span class="text-muted">-</span>',
                    $badStr,
                    $fmtSectors($metaDev['recovery_start_sectors'] ?? null),
                    $fmtSectors($metaDev['offset_sectors'] ?? null),
                    $pplStr,
                    htmlspecialchars((string) ($metaDev['id_model']        ?? '-')),
                    htmlspecialchars((string) ($metaDev['id_serial_short'] ?? '-')),
                    $sizeBytes > 0 ? LibreNMS\Util\Number::formatBi($sizeBytes) : '-',
                ];

                // Raw comparable values for client-side sorting (numeric columns parse as numbers).
                $sortVals = [
                    $path,
                    (string) ($metaDev['device_role'] ?? ''),
                    (string) ($dhEntry['val'] ?? -1),
                    is_numeric($metaDev['slot'] ?? null) ? (string) (int) $metaDev['slot'] : '',
                    $errVal !== null ? (string) $errVal : '',
                    $eventsVal !== null ? (string) (int) $eventsVal : '',
                    $badRaw !== null ? (string) (int) $badRaw : '',
                    $metaDev['recovery_start_sectors'] ?? '',
                    $metaDev['offset_sectors'] ?? '',
                    $pplSector ?? '',
                    (string) ($metaDev['id_model'] ?? ''),
                    (string) ($metaDev['id_serial_short'] ?? ''),
                    (string) $sizeBytes,
                ];

                $tds = '';
                foreach ($cells as $ci => $cellHtml) {
                    $sv = htmlspecialchars((string) ($sortVals[$ci] ?? ''));
                    $tds .= "<td data-sort=\"{$sv}\">{$cellHtml}</td>";
                }
                echo "<tr>{$tds}</tr>";
            @endphp
        @endforeach

        @php echo '</tbody></table>'; $panelEnd(); @endphp

        <style>
            #mdadm-drives-table thead th.mdadm-sort-asc::after  { content: " \25B2"; font-size: 9px; }
            #mdadm-drives-table thead th.mdadm-sort-desc::after { content: " \25BC"; font-size: 9px; }
        </style>
        <script>
        (function () {
            var table = document.getElementById('mdadm-drives-table');
            if (!table || !table.tHead || !table.tBodies.length) { return; }
            var tbody   = table.tBodies[0];
            var headers = table.tHead.rows[0].cells;

            function sortBy(idx, type, asc) {
                var rows = Array.prototype.slice.call(tbody.rows);
                rows.sort(function (a, b) {
                    var av = a.cells[idx] ? (a.cells[idx].getAttribute('data-sort') || '') : '';
                    var bv = b.cells[idx] ? (b.cells[idx].getAttribute('data-sort') || '') : '';
                    var cmp;
                    if (type === 'numeric') {
                        var an = parseFloat(av), bn = parseFloat(bv);
                        if (isNaN(an)) { an = -Infinity; }
                        if (isNaN(bn)) { bn = -Infinity; }
                        cmp = an - bn;
                    } else {
                        cmp = av.localeCompare(bv, undefined, { numeric: true, sensitivity: 'base' });
                    }
                    return asc ? cmp : -cmp;
                });
                rows.forEach(function (r) { tbody.appendChild(r); });
                for (var i = 0; i < headers.length; i++) {
                    headers[i].classList.remove('mdadm-sort-asc', 'mdadm-sort-desc');
                }
                headers[idx].classList.add(asc ? 'mdadm-sort-asc' : 'mdadm-sort-desc');
            }

            for (var i = 0; i < headers.length; i++) {
                (function (idx) {
                    headers[idx].addEventListener('click', function () {
                        var asc = !headers[idx].classList.contains('mdadm-sort-asc');
                        sortBy(idx, headers[idx].getAttribute('data-sort-type') || 'string', asc);
                    });
                })(i);
            }

            var def = table.getAttribute('data-default-sort');
            if (def !== null && def !== '') {
                sortBy(parseInt(def, 10), table.getAttribute('data-default-sort-type') || 'string', true);
            }
        })();
        </script>
    @endif

    {{-- Array graphs --}}
    @if($data->isLegacy)
        @php
            foreach ($legacyGraphs as $metric => $text) {
                $graph_array = [
                    'height' => '100', 'width' => '215',
                    'to'     => time(),
                    'id'     => $data->app->app_id,
                    'type'   => 'mdadm_legacy',
                    'metric' => $metric,
                    'array'  => $array,
                    'legend' => 'no',
                ];
                echo "<div class=\"panel panel-default\"><div class=\"panel-heading\"><h3 class=\"panel-title\">{$text}</h3></div><div class=\"panel-body\">";
                include 'includes/html/print-graphrow.inc.php';
                echo '</div></div>';
            }
        @endphp
    @else
        @php
            $diskioRates = mdadm_diskio_rates($data, $array);
            $syncData    = $data->syncDataForArray($array);

            $graphHeaders = [
                'disk_counts' => 'A:' . (int) ($meta['active_devices'] ?? 0)
                    . ' S:' . (int) ($meta['spare_devices'] ?? 0)
                    . ' F:' . (int) ($meta['failed_devices'] ?? 0)
                    . ' D:' . (int) ($meta['degraded'] ?? 0),
                'mismatch'    => isset($arrData['mdadm_array_mismatch']['val']) ? (string) (int) $arrData['mdadm_array_mismatch']['val'] : '-',
                'sync_bps'    => $syncData['speed_bps'] > 0
                    ? LibreNMS\Util\Number::formatBi($syncData['speed_bps'], suffix: 'B/s')
                    : '-',
                'sync_pct'    => number_format((float) $syncData['completed_pct'], 1) . '%',
                'diskio_ops'  => '-',
                'diskio_bits' => '-',
            ];

            if (isset($diskioRates['reads'], $diskioRates['writes'])) {
                $graphHeaders['diskio_ops'] = 'In: ' . LibreNMS\Util\Number::formatSi($diskioRates['reads'],  2, 0, 'ops/s')
                    . ' | Out: ' . LibreNMS\Util\Number::formatSi($diskioRates['writes'], 2, 0, 'ops/s');
            }
            if (isset($diskioRates['read'], $diskioRates['written'])) {
                $graphHeaders['diskio_bits'] = 'In: ' . LibreNMS\Util\Number::formatBi($diskioRates['read'],    2, 0, 'B/s')
                    . ' | Out: ' . LibreNMS\Util\Number::formatBi($diskioRates['written'], 2, 0, 'B/s');
            }

            foreach ($arrayGraphs as $key => $spec) {
                $graph_array = [
                    'height' => '100', 'width' => '215',
                    'to'     => App\Facades\LibrenmsConfig::get('time.now'),
                    'from'   => App\Facades\LibrenmsConfig::get('time.day'),
                    'id'     => $data->app->app_id,
                    'type'   => $spec['type'],
                    'array'  => $array,
                    'legend' => 'no',
                ];
                if (isset($spec['metric'])) { $graph_array['metric'] = $spec['metric']; }
                $text        = $spec['title'];
                $headerValue = htmlspecialchars((string) $graphHeaders[$key]);
                echo "<div class=\"panel panel-default\"><div class=\"panel-heading\"><h3 class=\"panel-title\">{$text}<span class=\"pull-right text-muted\">{$headerValue}</span></h3></div><div class=\"panel-body\">";
                include 'includes/html/print-graphrow.inc.php';
                echo '</div></div>';
            }
        @endphp
    @endif

@endif
