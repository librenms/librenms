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
    'Path'   => 'Block device path (e.g. sda5).',
    'Role'   => 'Role this device plays in the array: active member, spare, journal, or replacement.',
    'Health' => 'Device state flags from the kernel.',
    'Slot'   => 'Slot (raid_disk) this device occupies. -1 means spare.',
    'Errors' => 'Cumulative count of read errors on this device.',
    'Model'  => '',
    'Serial' => '',
    'Size'   => '',
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
        $names = array_keys($data->arrayData);
        foreach ($names as $i => $name) {
            $label = htmlspecialchars($name);
            if ($selectedArray === $name) {
                $label = "<span class=\"pagemenu-selected\">{$label}</span>";
            }
            echo generate_link($label, $linkArray, ['array' => $name]);
            if ($i < count($names) - 1) { echo ', '; }
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
                <th data-column-id="name"           data-sortable="true">MD Device</th>
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
            @foreach($data->arrayNames() as $name)
                @php
                    $arrData = $data->array($name);
                    $hEntry  = $arrData['mdadm_array_health_status'] ?? [];
                    $hBadge  = mdadm_badge($hEntry['label'] ?? 'Unknown', $hEntry['class'] ?? 'default', $hEntry['info'] ?? '');
                    $arrUrl  = LibreNMS\Util\Url::generate($linkArray + ['array' => $name]);
                    $nameEsc = htmlspecialchars($name);

                    $panelStart("<a href=\"{$arrUrl}\">{$nameEsc}</a>", $hBadge);
                    echo '<div class="row">';
                    foreach ($arrayGraphs as $spec) {
                        $graphArray = [
                            'height' => '80', 'width' => '180',
                            'type'   => $spec['type'],
                            'id'     => $data->app->app_id,
                            'array'  => $name,
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
        $name     = (string) $selectedArray;
        $arrData  = $data->array($name);
        $meta     = $data->arraysMeta[$name] ?? [];
        $sync     = $data->syncDataForArray($name);
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
                $mismatchVal = (int) ($arrData['mdadm_array_mismatch']['val'] ?? 0);
                $chunkSize   = (int) ($meta['chunk_size'] ?? 0);

                $panelStart(htmlspecialchars($name) . ' Status', $hBadge);
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
                    '<span' . ($mismatchVal > 0 ? ' class="text-warning"' : '') . '>' . $mismatchVal . '</span>',
                    'Number of sectors found inconsistent during the last check or repair operation.'
                );
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

    </div>{{-- /.row --}}

    {{-- Devices table --}}
    @php
        $sensorDevices = $arrData['devices'] ?? [];
        $metaDevices   = (array) ($data->arraysDevices[$name] ?? []);
    @endphp
    @if(!empty($metaDevices))
        @php
            $panelStart('Devices');
            $ths = '';
            foreach ($deviceHeaders as $h => $tip) {
                $tipAttr = $tip !== '' ? ' title="' . htmlspecialchars($tip) . '"' : '';
                $ths .= "<th{$tipAttr}>" . htmlspecialchars($h) . '</th>';
            }
            echo "<table class=\"table table-condensed table-hover\"><thead><tr>{$ths}</tr></thead><tbody>";
        @endphp

        @foreach($metaDevices as $devKey => $metaDev)
            @php
                $dev   = is_array($sensorDevices[$devKey] ?? null) ? $sensorDevices[$devKey] : [];
                $path  = (string) ($metaDev['path'] ?? $devKey);

                $dhEntry  = $dev['mdadm_device_health_status'] ?? [];
                $errVal   = (int) ($dev['mdadm_device_error']['val'] ?? $dev['mdadm_device_errors']['val'] ?? 0);
                $sizeBytes = (int) ($metaDev['size_bytes'] ?? 0);

                $cells = [
                    htmlspecialchars($path),
                    htmlspecialchars((string) ($metaDev['device_role']     ?? '-')),
                    mdadm_badge($dhEntry['label'] ?? 'Unknown', $dhEntry['class'] ?? 'default', $dhEntry['info'] ?? ''),
                    htmlspecialchars((string) ($metaDev['slot']            ?? '-')),
                    $errVal > 0 ? '<span class="text-warning">' . $errVal . '</span>' : (string) $errVal,
                    htmlspecialchars((string) ($metaDev['id_model']        ?? '-')),
                    htmlspecialchars((string) ($metaDev['id_serial_short'] ?? '-')),
                    $sizeBytes > 0 ? LibreNMS\Util\Number::formatBi($sizeBytes) : '-',
                ];
                echo '<tr><td>' . implode('</td><td>', $cells) . '</td></tr>';
            @endphp
        @endforeach

        @php echo '</tbody></table>'; $panelEnd(); @endphp
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
                    'array'  => $name,
                    'legend' => 'no',
                ];
                echo "<div class=\"panel panel-default\"><div class=\"panel-heading\"><h3 class=\"panel-title\">{$text}</h3></div><div class=\"panel-body\">";
                include 'includes/html/print-graphrow.inc.php';
                echo '</div></div>';
            }
        @endphp
    @else
        @php
            $diskioRates = mdadm_diskio_rates($data, $name);
            $syncData    = $data->syncDataForArray($name);

            $graphHeaders = [
                'disk_counts' => 'A:' . (int) ($meta['active_devices'] ?? 0)
                    . ' S:' . (int) ($meta['spare_devices'] ?? 0)
                    . ' F:' . (int) ($meta['failed_devices'] ?? 0)
                    . ' D:' . (int) ($meta['degraded'] ?? 0),
                'mismatch'    => (string) ((int) ($arrData['mdadm_array_mismatch']['val'] ?? 0)),
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
                    'array'  => $name,
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
