<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2015 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 * Copyright (c) 2016 Cercel Valentin <crc@nuamchefazi.ro>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

?>

<h3>Storage settings</h3>

<div class="table-responsive">
    <table id="storage" class="table table-hover table-condensed storage">
        <thead>
        <tr>
            <th data-column-id="hostname" data-header-css-class="edit-storage-device">Device</th>
            <th data-column-id="storage_descr" data-formatter="descr">Storage</th>
            <th data-column-id="storage_perc" data-header-css-class="edit-storage-input">Usage</th>
            <th data-column-id="storage_perc_warn" data-formatter="perc_update" data-header-css-class="edit-storage-input">% warn</th>
            <th data-column-id="storage_ignore" data-formatter="ignore" data-header-css-class="edit-storage-input">Ignore</th>
        </tr>
        </thead>
    </table>
</div>

<script>
    var grid = $("#storage").bootgrid({
        ajax: true,
        rowCount: [25,50,100,250,-1],
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
                return "<div class='form-group'><input type='text' class='form-control input-sm storage' id='storage_perc_warn_"+row.storage_id+"' data-device_id='<?php echo $device['device_id']; ?>' data-storage_id='"+row.storage_id+"' data-storage_perc='"+row.storage_perc_warn+"' value='"+row.storage_perc_warn+"'></div>";
            },
            "descr": function(column,row) {
                return "<div class='form-group'><input type='text' class='form-control input-sm storage' id='storage_descr_"+row.storage_id+"' data-device_id='<?php echo $device['device_id']; ?>' data-storage_id='"+row.storage_id+"' data-storage_descr='"+row.storage_descr+"' value='"+row.storage_descr+"'></div>";
            },
            "ignore": function(column,row) {
                if (row.storage_ignore == 1) {
                    var checkedyes = "selected='selected'";
                    var checkedno = "";
                } else {
                    var checkedyes = "";
                    var checkedno = "selected='selected'";
                }
                return "<div class='form-group'><select class='form-control input-sm storage' id='storage_ignore_"+row.storage_id+"' data-device_id='<?php echo $device['device_id']; ?>' data-storage_id='"+row.storage_id+"'><option value='1' "+checkedyes+">yes</option><option value='0' "+checkedno+">no</option></select></div>";
            }
        },
        templates: {
        }
    }).on("loaded.rs.jquery.bootgrid", function() {
        grid.find(".storage").blur(function(event) {
            event.preventDefault();
            var device_id = $(this).data("device_id");
            var storage_id = $(this).data("storage_id");

            var storage_descr = $(this).data("storage_descr");
            storage_descr = $('#storage_descr_'+storage_id).val();

            var storage_ignore = $(this).data("storage_ignore");
            storage_ignore = $('#storage_ignore_'+storage_id).val();

            var data = $(this).data("storage_perc_warn");
            data = $('#storage_perc_warn_'+storage_id).val();

            var $this = $(this);
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {type: "storage-update", device_id: device_id, data: data, storage_id: storage_id, storage_descr: storage_descr, storage_ignore: storage_ignore},
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
