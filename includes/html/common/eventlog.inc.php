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
    <table id="eventlog" class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th data-column-id="datetime" data-order="desc">Timestamp</th>
                <th data-column-id="type">Type</th>
                <th data-column-id="device_id">Hostname</th>
                <th data-column-id="message">Message</th>
                <th data-column-id="username">User</th>
            </tr>
        </thead>
    </table>
</div>
<script>

var eventlog_grid = $("#eventlog").bootgrid({
    ajax: true,
    rowCount: [50, 100, 250, -1],
    post: function ()
    {
        return {
            device: "' . (int) ($vars['device']) . '",
            eventtype: "' . addcslashes($vars['eventtype'], '"') . '",
        };
    },
    url: "' . url('/ajax/table/eventlog') . '"
});

</script>
';
