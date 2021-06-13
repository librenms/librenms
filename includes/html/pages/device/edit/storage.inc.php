<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2015 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

?>

<h3>Storage Settings</h3>

<div class="table-responsive">
    <table id="storage" class="table table-hover table-condensed storage">
        <thead>
        <tr>
            <th data-column-id="hostname">Device</th>
            <th data-column-id="storage_descr">Storage</th>
            <th data-column-id="storage_size">Size</th>
            <th data-column-id="storage_perc">%</th>
            <th data-column-id="storage_perc_warn" data-formatter="perc_update" data-header-css-class="edit-storage-input">% Warn</th>
        </tr>
        </thead>
    </table>
</div>

<script>
    var grid = $("#storage").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function ()
        {
            return {
                id: "storage-edit",
                device_id: <?php echo $device['device_id']; ?>,
            };
        },
        url: "ajax_table.php",
        formatters: {
            "perc_update": function(column,row) {
                return "<div class='form-group'><input type='text' class='form-control input-sm storage' data-device_id='<?php echo $device['device_id']; ?>' data-storage_id='"+row.storage_id+"' value='"+row.storage_perc_warn+"'></div>";
            }
        },
        templates: {
        }
    }).on("loaded.rs.jquery.bootgrid", function() {

        grid.find(".storage").on("blur", function(event) {
            event.preventDefault();
            var device_id = $(this).data("device_id");
            var storage_id = $(this).data("storage_id");
            var data = $(this).val();
            var $this = $(this);
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {type: "storage-update", device_id: device_id, data: data, storage_id: storage_id},
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        $this.closest('.form-group').addClass('has-success');
                        setTimeout(function () {
                            $this.closest('.form-group').removeClass('has-success');
                        }, 2000);
                    } else {
                        $this.closest('.form-group').addClass('has-error');
                        setTimeout(function () {
                            $this.closest('.form-group').removeClass('has-error');
                        }, 2000);
                    }
                },
                error: function () {
                    $this.closest('.form-group').addClass('has-error');
                    setTimeout(function () {
                        $this.closest('.form-group').removeClass('has-error');
                    }, 2000);
                }
            });
        });
    });
</script>
