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
$rrd_optionsb = '';
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

            $rrd_optionsb .= " LINE1.5:mempoolfree#e5e5e5:'Free Memory            ':STACK";
            $rrd_optionsb .= ' GPRINT:mempoolfree:MIN:%3.0lf%%';
            $rrd_optionsb .= ' GPRINT:mempoolfree:LAST:%3.0lf%%';
            $rrd_optionsb .= ' GPRINT:mempoolfree:MAX:%3.0lf%%';
            $rrd_optionsb .= ' GPRINT:mempoolfreebytes:LAST:%6.2lf%sB\\l';

            $stack = '';
            unset($free_indexes);
        }

        $rrd_optionsb .= " LINE1.5:mempooltotal$index#$color:'$descr'$stack";
        $rrd_optionsb .= " GPRINT:mempooltotal$index:MIN:%3.0lf%%";
        $rrd_optionsb .= " GPRINT:mempooltotal$index:LAST:%3.0lf%%";
        $rrd_optionsb .= " GPRINT:mempooltotal$index:MAX:%3.0lf%%";
        $rrd_optionsb .= " GPRINT:mempoolused$index:LAST:%6.2lf%sB\\l ";
    }
}

$rrd_options .= $rrd_optionsb;

$rrd_options .= ' HRULE:0#999999';
