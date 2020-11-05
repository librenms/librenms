<?php

require 'includes/html/graphs/common.inc.php';

$rrd_options .= ' -u 100 -l 0 -E -b 1024 ';
$rrd_options .= " COMMENT:'                            Min   Cur    Max      Used\\n'";

// order mempools properly
$mempool_classes = [
    'system' => 0,
    'buffers' => 1,
    'cached' => 2,
    'available' => 3,
    'shared' => 4,
    'swap' => 5,
    'virtual' => 6,
];
$mempools = DeviceCache::get($device['device_id'])->mempools->sort(function (\App\Models\Mempool $a_weight, \App\Models\Mempool $b_weight) use ($mempool_classes) {
    $a_weight = $mempool_classes[$a_weight->mempool_class] ?? 99;
    $b_weight = $mempool_classes[$b_weight->mempool_class] ?? 99;
    if ($a_weight == $b_weight) {
        return 0;
    }

    return $a_weight < $b_weight ? -1 : 1;
})->values(); // reset keys

$colors = \LibreNMS\Config::get('graph_colours.varied');
$legend_sections = [];
$section = 0;
$free_indexes = [];

/** @var \App\Models\Mempool $mempool */
foreach ($mempools as $index => $mempool) {
    $color = $colors[$index % 8];

    $descr = rrdtool_escape($mempool->mempool_descr, 22);
    $rrd_filename = rrd_name($device['hostname'], ['mempool', $mempool->mempool_type, $mempool->mempool_class, $mempool->mempool_index]);

    if (rrdtool_check_rrd_exists($rrd_filename)) {
        $rrd_options .= " DEF:mempoolfree$index=$rrd_filename:free:AVERAGE ";
        $rrd_options .= " DEF:mempoolused$index=$rrd_filename:used:AVERAGE ";
        $rrd_options .= " CDEF:mempooltotal$index=mempoolused$index,mempoolused$index,mempoolfree$index,+,/,100,* ";

        $stack = '';
        if (in_array($mempool->mempool_class, ['system', 'cached', 'buffers'])) {
            $free_indexes[] = $index;
            if ($index > 0) {
                $stack = ':STACK';
            }
            $rrd_options .= " AREA:mempooltotal$index#{$color}60:$stack";
        } elseif (! empty($free_indexes)) {
            $rrd_options .= ' CDEF:mempoolfree=100,mempooltotal' . implode(',mempooltotal', $free_indexes) . str_repeat(',-', count($free_indexes));
            $rrd_options .= " CDEF:mempoolfreebytes=mempoolfree{$free_indexes[0]},mempoolused{$free_indexes[0]},+,mempoolfree,100,/,*";
            $rrd_options .= ' AREA:mempoolfree#e5e5e550:STACK';
            $legend_sections[1] .= " COMMENT:'  Free Memory            '";
            $legend_sections[1] .= ' GPRINT:mempoolfree:MIN:%3.0lf%%';
            $legend_sections[1] .= ' GPRINT:mempoolfree:LAST:%3.0lf%%';
            $legend_sections[1] .= ' GPRINT:mempoolfree:MAX:%3.0lf%%';
            $legend_sections[1] .= ' GPRINT:mempoolfreebytes:LAST:%6.2lf%sB\l';

            $rrd_options .= " CDEF:mempoolavailablebytes=mempoolfree{$free_indexes[0]}";
            $rrd_options .= " CDEF:mempoolavailable=100,mempooltotal{$free_indexes[0]},-";
            $legend_sections[1] .= " COMMENT:'  Available Memory       '";
            $legend_sections[1] .= ' GPRINT:mempoolavailable:MIN:%3.0lf%%';
            $legend_sections[1] .= ' GPRINT:mempoolavailable:LAST:%3.0lf%%';
            $legend_sections[1] .= ' GPRINT:mempoolavailable:MAX:%3.0lf%%';
            $legend_sections[1] .= ' GPRINT:mempoolavailablebytes:LAST:%6.2lf%sB\l';

            $stack = '';
            unset($free_indexes);
        }

        if ($mempool->mempool_class == 'swap') {
            $section = 2;
        }

        $legend_sections[$section] .= " LINE1.5:mempooltotal$index#$color:'$descr'$stack";
        $legend_sections[$section] .= " GPRINT:mempooltotal$index:MIN:%3.0lf%%";
        $legend_sections[$section] .= " GPRINT:mempooltotal$index:LAST:%3.0lf%%";
        $legend_sections[$section] .= " GPRINT:mempooltotal$index:MAX:%3.0lf%%";
        $legend_sections[$section] .= " GPRINT:mempoolused$index:LAST:%6.2lf%sB\\l ";
    }
}

$rrd_options .= implode(" COMMENT:' \\l'", $legend_sections);

$rrd_options .= ' HRULE:0#999999';
