<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2018 TheGreatDoc
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

?>
<h3>Routing settings</h3>

<div class="table-responsive">
    <table id="routing" class="table table-hover table-condensed routing">
        <thead>
        <tr>
            <th data-column-id="hostname">Device</th>
            <th data-column-id="bgpPeerIdentifier">Peer address</th>
            <th data-column-id="bgpPeerRemoteAs">Remote AS</th>
            <th data-column-id="bgpPeerDescr" data-formatter="descr_update" data-header-css-class="edit-routing-input">Description</th>
        </tr>
        </thead>
    </table>
</div>

<script>
    var grid = $("#routing").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function ()
        {
            return {
                id: "routing-edit",
                device_id: <?php echo $device['device_id']; ?>,
            };
        },
        url: "ajax_table.php",
        formatters: {
            "descr_update": function(column,row) {
                return "<div class='form-group'><input type='text' class='form-control input-sm routing' data-device_id='<?php echo $device['device_id']; ?>' data-routing_id='"+row.routing_id+"' value='"+row.bgpPeerDescr+"'></div>";
            }
        },
        templates: {
        }
    }).on("loaded.rs.jquery.bootgrid", function() {

        grid.find(".routing").on("blur", function(event) {
            event.preventDefault();
            var device_id = $(this).data("device_id");
            var routing_id = $(this).data("routing_id");
            var data = $(this).val();
            var $this = $(this);
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {type: "routing-update", device_id: device_id, data: data, routing_id: routing_id},
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        toastr.success(data.message);
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function () {
                    toastr.error(data.message);
                }
            });
        });
    });
</script>
