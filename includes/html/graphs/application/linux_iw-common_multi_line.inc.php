<?php

if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($rrdArray as $rrdVar => $rrdValues) {
        $rrd_list[] = [
            'cdef_rpn'   => $rrdValues['cdef_rpn'] ?? null,
            'colour'     => $rrdValues['colour'] ?? null,
            'descr'      => $rrdValues['descr'],
            'divider'    => $rrdValues['divider'] ?? null,
            'ds'         => $rrdVar,
            'filename'   => $rrd_filename,
            'multiplier' => $rrdValues['multiplier'] ?? null,
        ];
    }
} else {
    d_echo('RRD ' . $rrd_filename . ' not found');
}
