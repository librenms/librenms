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

?>
<div class="panel panel-default panel-condensed">
    <div class="panel-heading">
        <div class="row" style="padding:0px 10px 0px 10px;">
        <div class="pull-left">
            <?php echo $linkoptions; ?>
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
        post: function ()
        {
            return {
                id:         'wireless-sensors',
                view:       '<?php echo $vars['view']; ?>',
                graph_type: '<?php echo $graph_type; ?>',
                unit:       '<?php echo $unit; ?>',
                class:      '<?php echo $class; ?>'
            };
        },
        url: "ajax_table.php"
    });
</script>
