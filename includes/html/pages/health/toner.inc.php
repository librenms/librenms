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

$pagetitle[] = 'Health :: Toner';
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
        <table id="toner" class="table table-hover table-condensed mempool">
            <thead>
            <tr>
                <th data-column-id="hostname">Device</th>
                <th data-column-id="supply_descr">Toner</th>
                <th data-column-id="supply_type" data-searchable="false">Type</th>
                <th data-column-id="graph" data-sortable="false" data-searchable="false"></th>
                <th data-column-id="toner_used" data-searchable="false">Used</th>
                <th data-column-id="supply_current" data-searchable="false">Usage</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
<script>
    var grid = $("#toner").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function ()
        {
            return {
                id: "toner",
                view: '<?php echo $vars['view']; ?>'
            };
        },
        url: "ajax_table.php"
    });
</script>
