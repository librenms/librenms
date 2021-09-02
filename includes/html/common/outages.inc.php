<?php
/*
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

$common_output[] = '
<div class="table-responsive">
    <table id="outages" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th data-column-id="status" data-sortable="false"></th>
                <th data-column-id="going_down" data-order="desc">Start</th>
                <th data-column-id="up_again">End</th>
                <th data-column-id="device_id">Hostname</th>
                <th data-column-id="duration" data-sortable="false">Duration</th>
            </tr>
        </thead>
    </table>
</div>
<script>

var outages_grid = $("#outages").bootgrid({
    ajax: true,
    rowCount: [50, 100, 250, -1],
    templates: {
        search: ""
    },
    post: function ()
    {
        return {
            device: "' . (int) ($vars['device']) . '",
            to: "' . addcslashes($vars['to'], '"') . '",
            from: "' . addcslashes($vars['from'], '"') . '",
        };
    },
    url: "' . url('/ajax/table/outages') . '"
});

</script>
';
