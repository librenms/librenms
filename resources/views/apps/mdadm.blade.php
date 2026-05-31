@php
$arrayGraphs = [
    'health'      => ['type' => 'mdadm_health',      'title' => 'Array Health'],
    'disk_counts' => ['type' => 'mdadm_disk_counts', 'title' => 'Disk Counts'],
    'mismatch'    => ['type' => 'mdadm_mismatch',                               'title' => 'Mismatch'],
    'sync_bps'    => ['type' => 'mdadm_sync_bps',    'title' => 'Sync Speed'],
    'sync_pct'    => ['type' => 'mdadm_sync_pct',    'title' => 'Sync Progress'],
    'diskio_ops'  => ['type' => 'mdadm_diskio_ops',                             'title' => 'Disk I/O Ops'],
    'diskio_bits' => ['type' => 'mdadm_diskio_bits',                            'title' => 'Disk I/O Bytes'],
];

$graphKey  = null;
$graphMode = null;
foreach ($arrayGraphs as $key => $spec) {
    if ($vars['view'] === $key) {
        $graphKey  = $key;
        $graphMode = 'graphrow';
        break;
    }
    if ($vars['view'] === $key . '_mini') {
        $graphKey  = $key;
        $graphMode = 'mini';
        break;
    }
}
@endphp

{{-- Optionbar --}}
@php
    print_optionbar_start();
    echo '<span style="font-weight:bold;">mdadm RAID</span> &#187; ';

    if ($vars['view'] === 'arrays') { echo "<span class='pagemenu-selected'>"; }
    echo generate_link('Arrays', $vars, ['view' => 'arrays']);
    if ($vars['view'] === 'arrays') { echo '</span>'; }

    echo ' | Graphs: ';
    $sep = '';
    foreach ($arrayGraphs as $key => $spec) {
        $isGraphrow = $vars['view'] === $key;
        $isMini     = $vars['view'] === $key . '_mini';

        echo $sep;

        if ($isGraphrow) { echo "<span class='pagemenu-selected'>"; }
        echo generate_link($spec['title'], $vars, ['view' => $key]);
        if ($isGraphrow) { echo '</span>'; }

        echo ' (';

        if ($isMini) { echo "<span class='pagemenu-selected'>"; }
        echo generate_link('mini', $vars, ['view' => $key . '_mini']);
        if ($isMini) { echo '</span>'; }

        echo ')';

        $sep = ' | ';
    }
    unset($sep);

    print_optionbar_end();
@endphp

@if($vars['view'] === 'arrays')
    <table id="mdadm-arrays-table"
           class="table table-condensed table-responsive table-striped"
           data-url="{{ route('table.mdadm-array') }}">
        <thead>
        <tr>
            <th data-column-id="device"         data-sortable="false">Device</th>
            <th data-column-id="array_name"     data-sortable="true">Array Name</th>
            <th data-column-id="md_id"          data-sortable="true">MD Device</th>
            <th data-column-id="health"         data-sortable="false">Health</th>
            <th data-column-id="sync_action"    data-sortable="true">Operation</th>
            <th data-column-id="state"          data-sortable="true">State</th>
            <th data-column-id="level"          data-sortable="true">Level</th>
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
        $("#mdadm-arrays-table").bootgrid({ ajax: true });
    </script>
@elseif($graphKey !== null)
    @php
        $spec   = $arrayGraphs[$graphKey];
        $arrays = App\Models\MdadmArray::with(['application.device'])->get();
    @endphp

    @if($graphMode === 'mini')
        <div style="display:flex;flex-wrap:wrap;gap:12px;padding:8px">
        @foreach($arrays as $arr)
            @php
                $dev = $arr->application->device ?? null;
                if (! $dev) { continue; }
                $arrUrl = LibreNMS\Util\Url::generate([
                    'page'   => 'device',
                    'device' => $dev->device_id,
                    'tab'    => 'apps',
                    'app'    => 'mdadm',
                    'array'  => $arr->md_id,
                ]);
                $graph_array = [
                    'height' => '80',
                    'width'  => '180',
                    'type'   => $spec['type'],
                    'id'     => $arr->app_id,
                    'array'  => $arr->md_id ?? $arr->uuid,
                    'from'   => App\Facades\LibrenmsConfig::get('time.day'),
                    'to'     => App\Facades\LibrenmsConfig::get('time.now'),
                    'legend' => 'no',
                ];
                if (isset($spec['metric'])) { $graph_array['metric'] = $spec['metric']; }
                $label    = htmlspecialchars($dev->hostname . ' / ' . ($arr->md_id ?? $arr->uuid));
                $graphTag = LibreNMS\Util\Url::lazyGraphTag($graph_array);
            @endphp
            <div class="pull-left" style="margin-right:8px;margin-bottom:8px">
                <div class="text-muted" style="font-size:11px;margin-bottom:4px">{{ $label }}</div>
                <a href="{{ $arrUrl }}">{!! $graphTag !!}</a>
            </div>
        @endforeach
        </div>
    @else
        @php
            foreach ($arrays as $arr) {
                $dev = $arr->application->device ?? null;
                if (! $dev) { continue; }
                $graph_array = [
                    'type'  => $spec['type'],
                    'id'    => $arr->app_id,
                    'array' => $arr->md_id ?? $arr->uuid,
                    'to'    => App\Facades\LibrenmsConfig::get('time.now'),
                ];
                if (isset($spec['metric'])) { $graph_array['metric'] = $spec['metric']; }
                $label = htmlspecialchars($dev->hostname . ' / ' . ($arr->md_id ?? $arr->uuid));
                echo '<div class="panel panel-default">'
                    . '<div class="panel-heading"><h3 class="panel-title">' . $label . '</h3></div>'
                    . '<div class="panel-body"><div class="row">';
                include 'includes/html/print-graphrow.inc.php';
                echo '</div></div></div>';
            }
        @endphp
    @endif
@endif
