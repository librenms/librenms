<?php
/*
 * LibreNMS global MIB association viewer
 *
 * Copyright (c) 2015 Gear Consulting Pty Ltd <github@libertysys.com.au>
 *
 * Author: Paul Gear
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (\LibreNMS\Config::get("poller_modules.mib")) {
?>

<h4>MIB associations for all devices</h4>
<div class="table-responsive">
    <table id="mibs" class="table table-hover table-condensed mibs">
        <thead>
            <tr>
                <th data-column-id="hostname">Hostname</th>
                <th data-column-id="module">Module</th>
                <th data-column-id="mib">MIB</th>
                <th data-column-id="included_by">Included by</th>
                <th data-column-id="last_modified">Last Modified</th>
            </tr>
        </thead>
    </table>
</div>

<script>
    var grid = $("#mibs").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function ()
        {
            return {
                id: "device_mibs",
            };
        },
        url: "ajax_table.php",
        formatters: {
        },
        templates: {
        }
    });
</script>
<?php
} else {
    print_mib_poller_disabled();
}
