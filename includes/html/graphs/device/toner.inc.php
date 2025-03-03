<?php

require 'includes/html/graphs/common.inc.php';

$rrd_options .= ' -l 0 -E ';

$iter = '1';
$rrd_options .= " COMMENT:'Toner level            Cur     Min      Max\\n'";
foreach (dbFetchRows('SELECT * FROM printer_supplies where device_id = ?', [$device['device_id']]) as $toner) {
    $colour = toner2colour($toner['supply_descr'], 100 - $toner['supply_current']);

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
        }//end switch
    }//end if

    $hostname = gethostbyid($toner['device_id']);

    $descr = \LibreNMS\Data\Store\Rrd::safeDescr(substr(str_pad($toner['supply_descr'], 16), 0, 16));
    $rrd_filename = Rrd::name($device['hostname'], ['toner', $toner['supply_type'], $toner['supply_index']]);
    $id = $toner['supply_id'];

    $rrd_options .= " DEF:toner$id=$rrd_filename:toner:AVERAGE";
    $rrd_options .= " LINE2:toner$id#" . $colour['left'] . ":'" . $descr . "'";
    $rrd_options .= " GPRINT:toner$id:LAST:'%5.0lf%%'";
    $rrd_options .= " GPRINT:toner$id:MIN:'%5.0lf%%'";
    $rrd_options .= " GPRINT:toner$id:MAX:%5.0lf%%\l";

    $iter++;
}//end foreach
