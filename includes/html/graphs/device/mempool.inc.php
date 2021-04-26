<?php

use App\Models\Mempool;

require 'includes/html/graphs/common.inc.php';

$rrd_options .= ' -E -b 1024 ';

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
$mempools = DeviceCache::get($device['device_id'])->mempools->sort(function (Mempool $a_weight, Mempool $b_weight) use ($mempool_classes) {
    $a_weight = $mempool_classes[$a_weight->mempool_class] ?? 99;
    $b_weight = $mempool_classes[$b_weight->mempool_class] ?? 99;
    if ($a_weight == $b_weight) {
        return 0;
    }

    return $a_weight < $b_weight ? -1 : 1;
})->values();

// find available
$available = null;
$swap_present = false;
foreach ($mempools as $index => $mempool) {
    if ($mempool->mempool_class == 'available') {
        $available = $mempool;
        $mempools->forget($index);
    } elseif ($mempool->mempool_class == 'swap') {
        $swap_present = true;
    }
}

if (! $swap_present) {
    $rrd_options .= '-l 0 '; // swap is negative axis
}

$colors = \LibreNMS\Config::get('graph_colours.varied');
$legend_sections = [0 => '', 1 => ''];
$section = 0;
$free_indexes = [];
$rrd_options .= " COMMENT:'                            Min   Max    Cur      \\n'";

/** @var \App\Models\Mempool $mempool */
foreach ($mempools as $index => $mempool) {
    $color = $colors[$index % 8];

    $descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($mempool->mempool_descr, 22);
    $rrd_filename = Rrd::name($device['hostname'], ['mempool', $mempool->mempool_type, $mempool->mempool_class, $mempool->mempool_index]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_options .= " DEF:mempoolfree$index=$rrd_filename:free:AVERAGE ";
        $rrd_options .= " DEF:mempoolused$index=$rrd_filename:used:AVERAGE ";
        $rrd_options .= " CDEF:mempooltotal$index=mempoolused$index,mempoolfree$index,+ ";
        $rrd_options .= " CDEF:mempoolpercent$index=mempoolused$index,mempooltotal$index,/,100,* ";

        $system_pools = in_array($mempool->mempool_class, ['system', 'cached', 'buffers']);
        $stack = $system_pools && $index > 0 ? ':STACK' : '';
        if ($system_pools) {
            $free_indexes[] = $index;
            $rrd_options .= " AREA:mempoolused$index#{$color}70:$stack";
        }

        if ($mempool->mempool_class == 'system') {
            // add system
            $legend_sections[1] .= " LINE1:mempooltotal$index#AAAAAA:'Total                                     '";
            $legend_sections[1] .= " GPRINT:mempooltotal$index:LAST:%6.2lf%siB\\l ";
        }
        if ($mempool->mempool_class == 'swap') {
            $section = 2;
            $rrd_options .= " CDEF:mempoolswap$index=mempoolused$index,-1,* ";
            $rrd_options .= " AREA:mempoolswap$index#{$color}70:$stack";
            $legend_sections[$section] .= " LINE1.5:mempoolswap$index#$color:'$descr'$stack";
        } elseif ($mempool->mempool_class == 'virtual') {
            $section = 2;
            $legend_sections[$section] .= " COMMENT:'  $descr'";
        } else {
            $legend_sections[$section] .= " LINE1.5:mempoolused$index#$color:'$descr'$stack";
        }

        $legend_sections[$section] .= " GPRINT:mempoolpercent$index:MIN:%3.0lf%%";
        $legend_sections[$section] .= " GPRINT:mempoolpercent$index:MAX:%3.0lf%%";
        $legend_sections[$section] .= " GPRINT:mempoolpercent$index:LAST:%3.0lf%%";
        $legend_sections[$section] .= " GPRINT:mempoolused$index:LAST:%6.2lf%siB\\l ";
    }
}

// add free/availability calculations if we have buffers/cached
if (! empty($free_indexes)) {
    $mempool_classes = $mempools->pluck('mempool_class');
    if ($mempool_classes->contains('buffers') || $mempool_classes->contains('cached')) {
        $rrd_options .= ' CDEF:mempoolfree=100,mempoolpercent' . implode(',mempoolpercent', $free_indexes) . str_repeat(',-', count($free_indexes));
        $rrd_options .= " CDEF:mempoolfreebytes=mempoolfree{$free_indexes[0]},mempoolused{$free_indexes[0]},+,mempoolfree,100,/,*";
        $legend_sections[1] .= " COMMENT:'  Free memory            '";
        $legend_sections[1] .= ' GPRINT:mempoolfree:MIN:%3.0lf%%';
        $legend_sections[1] .= ' GPRINT:mempoolfree:LAST:%3.0lf%%';
        $legend_sections[1] .= ' GPRINT:mempoolfree:MAX:%3.0lf%%';
        $legend_sections[1] .= ' GPRINT:mempoolfreebytes:LAST:%6.2lf%siB\l';

        if ($available === null) {
            $rrd_options .= " CDEF:mempoolavailablebytes=mempoolfree{$free_indexes[0]}";
        } else {
            $available_filename = Rrd::name($device['hostname'], ['mempool', $available->mempool_type, $available->mempool_class, $available->mempool_index]);
            $rrd_options .= " DEF:mempoolavailablebytes=$available_filename:free:AVERAGE";
        }

        $rrd_options .= " CDEF:mempoolavailable=100,mempoolpercent{$free_indexes[0]},-";
        $legend_sections[1] .= " COMMENT:'  Available memory       '";
        $legend_sections[1] .= ' GPRINT:mempoolavailable:MIN:%3.0lf%%';
        $legend_sections[1] .= ' GPRINT:mempoolavailable:LAST:%3.0lf%%';
        $legend_sections[1] .= ' GPRINT:mempoolavailable:MAX:%3.0lf%%';
        $legend_sections[1] .= ' GPRINT:mempoolavailablebytes:LAST:%6.2lf%siB\l';
    }
}

$rrd_options .= implode(" COMMENT:' \\l'", $legend_sections);

$rrd_options .= ' HRULE:0#999999';
