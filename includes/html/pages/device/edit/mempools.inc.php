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

<h3>Memory settings</h3>

<div class="table-responsive">
    <table id="mempool" class="table table-hover table-condensed mempool">
        <thead>
        <tr>
            <th data-column-id="hostname">Device</th>
            <th data-column-id="mempool_descr">Memory</th>
            <th data-column-id="mempool_perc">%</th>
            <th data-column-id="mempool_perc_warn" data-formatter="perc_update" data-header-css-class="edit-mempool-input">% Warn</th>
        </tr>
        </thead>
    </table>
</div>

<script>
    var grid = $("#mempool").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function ()
        {
            return {
                id: "mempool-edit",
                device_id: <?php echo $device['device_id']; ?>,
            };
        },
        url: "ajax_table.php",
        formatters: {
            "perc_update": function(column,row) {
                return "<div class='form-group'><input type='text' class='form-control input-sm mempool' data-device_id='<?php echo $device['device_id']; ?>' data-mempool_id='"+row.mempool_id+"' value='"+row.mempool_perc_warn+"'></div>";
            }
        },
        templates: {
        }
    }).on("loaded.rs.jquery.bootgrid", function() {

        grid.find(".mempool").on("blur", function(event) {
            event.preventDefault();
            var device_id = $(this).data("device_id");
            var mempool_id = $(this).data("mempool_id");
            var data = $(this).val();
            var $this = $(this);
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {type: "mempool-update", device_id: device_id, data: data, mempool_id: mempool_id},
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
