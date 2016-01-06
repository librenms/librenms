<?php
/*
 * LibreNMS device MIB browser
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

if (!isset($vars['section'])) {
    $vars['section'] = "mib";
}
if (is_module_enabled('poller', 'mib')) {
?>

<h4><i class="fa fa-file-text-o"></i> Device MIB associations</h4>
<div class="table-responsive">
    <table id="mibs" class="table table-hover table-condensed mibs">
        <thead>
            <tr>
                <th data-column-id="module">Module</th>
                <th data-column-id="mib">MIB</th>
                <th data-column-id="included_by">Included by</th>
                <th data-column-id="last_modified">Last Modified</th>
            </tr>
        </thead>
    </table>
</div>

<h4><i class="fa fa-file-text-o"></i> Device MIB values</h4>
<div class="table-responsive">
    <table id="oids" class="table table-hover table-condensed mibs">
        <thead>
            <tr>
                <th data-column-id="module">Module</th>
                <th data-column-id="mib">MIB</th>
                <th data-column-id="object_type">Object type</th>
                <th data-column-id="oid">OID</th>
                <th data-column-id="value">Value</th>
                <th data-column-id="numvalue">Numeric Value</th>
                <th data-column-id="last_modified">Last Modified</th>
            </tr>
        </thead>
    </table>
</div>

<script>
    var grid = $("#mibs").bootgrid({
        ajax: true,
        rowCount: [50,100,250,-1],
        post: function ()
        {
            return {
                id: "device_mibs",
                device_id: '<?php echo htmlspecialchars($device['device_id']); ?>',
            };
        },
        url: "/ajax_table.php",
        formatters: {
        },
        templates: {
        }
    });
</script>

<script>
    var grid2 = $("#oids").bootgrid({
        ajax: true,
        rowCount: [50,100,250,-1],
        post: function ()
        {
            return {
                id: "device_oids",
                device_id: '<?php echo htmlspecialchars($device['device_id']); ?>',
            };
        },
        url: "/ajax_table.php",
        formatters: {
        },
        templates: {
        }
    });
</script>
<?php
}
else {
    print_mib_poller_disabled();
}
