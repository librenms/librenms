<?php

use App\Facades\DeviceCache;
use App\Facades\Rrd;
use LibreNMS\Util\Color;

require 'includes/html/graphs/common.inc.php';
$device = DeviceCache::get((int) $toner['device_id']);

$graph_params->scale_min = 0;

$iter = '1';
$rrd_options[] = 'COMMENT:Toner level            Cur     Min      Max\\n';
foreach ($device->printerSupplies as $toner) {
    $colour = Color::toner($toner->supply_descr, 100 - $toner->supply_current);

    if ($colour['left'] == null) {
        // FIXME generic colour function
        switch ($iter) {
            case '1':
                $colour['left'] = '000000';
                break;

            case '2':
                $colour['left'] = '008C00';
                break;

            case '3':
                $colour['left'] = '4096EE';
                break;

            case '4':
                $colour['left'] = '73880A';
                break;

            case '5':
                $colour['left'] = 'D01F3C';
                break;

            case '6':
                $colour['left'] = '36393D';
                break;

            case '7':
            default:
                $colour['left'] = 'FF0000';
                unset($iter);
                break;
        } //end switch
    } //end if

    $descr = Rrd::safeDescr(substr(str_pad($toner->supply_descr, 16), 0, 16));
    $rrd_filename = Rrd::name($device->hostname, ['toner', $toner->supply_type, $toner->supply_index]);

    $rrd_options[] = 'DEF:toner' . $toner->supply_id . '=' . $rrd_filename . ':toner:AVERAGE';
    $rrd_options[] = 'LINE2:toner' . $toner->supply_id . '#' . $colour['left'] . ':' . $descr;
    $rrd_options[] = 'GPRINT:toner' . $toner->supply_id . ':LAST:%5.0lf%%';
    $rrd_options[] = 'GPRINT:toner' . $toner->supply_id . ':MIN:%5.0lf%%';
    $rrd_options[] = 'GPRINT:toner' . $toner->supply_id . ":MAX:%5.0lf%%\l";

    $iter++;
}//end foreach
