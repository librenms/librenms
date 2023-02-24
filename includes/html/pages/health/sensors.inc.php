<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

switch ($class) {
    case 'charge':
        $pagetitle[] = 'Health :: Charge';
        break;
    case 'cooling':
        $pagetitle[] = 'Health :: Cooling';
        break;
    case 'current':
        $pagetitle[] = 'Health :: Current';
        break;
    case 'dbm':
        $pagetitle[] = 'Health :: dBm';
        break;
    case 'fanspeed':
        $pagetitle[] = 'Health :: Fanspeed';
        break;
    case 'frequency':
        $pagetitle[] = 'Health :: Frequency';
        break;
    case 'humidity':
        $pagetitle[] = 'Health :: Humidity';
        break;
    case 'load':
        $pagetitle[] = 'Health :: Load';
        break;
    case 'power':
        $pagetitle[] = 'Health :: Power';
        break;
    case 'pressure':
        $pagetitle[] = 'Health :: Pressure';
        break;
    case 'runtime':
        $pagetitle[] = 'Health :: Runtime';
        break;
    case 'signal':
        $pagetitle[] = 'Health :: Signal';
        break;
    case 'snr':
        $pagetitle[] = 'Health :: SNR';
        break;
    case 'state':
        $pagetitle[] = 'Health :: State';
        break;
    case 'count':
        $pagetitle[] = 'Health :: Count';
        break;
    case 'temperature':
        $pagetitle[] = 'Health :: Temperature';
        break;
    case 'voltage':
        $pagetitle[] = 'Health :: Voltage';
        break;
}

?>
<div class="panel panel-default panel-condensed">
    <div class="panel-heading">
        <div class="row" style="padding:0px 10px 0px 10px;">
            <div class="pull-left">
                <?php echo $navbar; ?>
            </div>

            <div class="pull-right">
                <?php echo $displayoptions; ?>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table id="sensors" class="table table-hover table-condensed storage">
            <thead>
            <tr>
                <th data-column-id="hostname">Device</th>
                <th data-column-id="sensor_descr">Sensor</th>
                <th data-column-id="graph" data-sortable="false" data-searchable="false"></th>
                <th data-column-id="alert" data-sortable="false" data-searchable="false"></th>
                <th data-column-id="sensor_current">Current</th>
                <th data-column-id="sensor_limit_low" data-searchable="false">Low Limit</th>
                <th data-column-id="sensor_limit" data-searchable="false">High Limit</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
<script>
    var grid = $("#sensors").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function () {
            return {
                id: 'sensors',
                view: '<?php echo $vars['view']; ?>',
                graph_type: '<?php echo $graph_type; ?>',
                unit: '<?php echo $unit; ?>',
                class: '<?php echo $class; ?>'
            };
        },
        url: "ajax_table.php"
    });
</script>
