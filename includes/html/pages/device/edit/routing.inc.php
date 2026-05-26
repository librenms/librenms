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

use App\Facades\DeviceCache;
use Illuminate\Support\Facades\Gate;

$device = DeviceCache::getPrimary();
$device_id = $device->device_id;
$snmp_contexts_raw = $device->getAttrib('routing_snmp_contexts', '[]');
$snmp_contexts = json_decode((string) $snmp_contexts_raw, true);

if (isset($_POST['editing'])) {
    if (Gate::allows('update', $device)) {
        $snmp_contexts = $_POST['snmp_contexts'] ?? [];
        if (! is_array($snmp_contexts)) {
            $snmp_contexts = [];
        }
        $snmp_contexts = array_values(array_unique(array_filter(array_map(
            fn ($context) => trim((string) $context),
            $snmp_contexts
        ))));

        if ($snmp_contexts !== []) {
            $device->setAttrib('routing_snmp_contexts', json_encode($snmp_contexts));
        } else {
            $device->forgetAttrib('routing_snmp_contexts');
        }

        toast()->success('SNMP contexts updated');
    }
}

?>
<form id="routing-contexts" name="routing-contexts" method="post" action="" role="form" class="form-horizontal">
    <?php echo csrf_field() ?>
    <input type="hidden" name="editing" value="yes">
    <div class="form-group">
        <label for="snmp_contexts" class="col-sm-2 control-label">Routing SNMP Contexts</label>
        <div class="col-sm-4">
            <select id="snmp_contexts" name="snmp_contexts[]" class="form-control" multiple>
                <?php foreach ($snmp_contexts as $context) { ?>
                    <option value="<?php echo htmlspecialchars($context, ENT_QUOTES); ?>" selected><?php echo htmlspecialchars($context); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-sm-2">
            <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Save</button>
        </div>
    </div>
</form>

<div class="table-responsive">
    <table id="routing" class="table table-hover table-condensed routing">
        <thead>
        <tr>
            <th data-column-id="hostname">Device</th>
            <th data-column-id="bgpPeerIdentifier">Peer address</th>
            <th data-column-id="bgpPeerRemoteAs">Remote AS</th>
            <th data-column-id="context_name">Context</th>
            <th data-column-id="bgpPeerDescr" data-formatter="descr_update" data-header-css-class="edit-routing-input">Description</th>
        </tr>
        </thead>
    </table>
</div>

<script>
    init_select2('#snmp_contexts', 'snmp-contexts', {}, null, 'Type context and press Enter', {
        tags: true,
        multiple: true,
        ajax: null,
        createTag: function (params) {
            var term = $.trim(params.term);

            if (term === '') {
                return null;
            }

            return {
                id: term,
                text: term
            };
        }
    });

    var grid = $("#routing").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function ()
        {
            return {
                id: "routing-edit",
                device_id: <?php echo $device_id; ?>,
            };
        },
        url: "ajax_table.php",
        formatters: {
            "descr_update": function(column,row) {
                return "<div class='form-group'><input type='text' class='form-control input-sm routing' data-device_id='<?php echo $device_id; ?>' data-routing_id='"+row.routing_id+"' value='"+row.bgpPeerDescr+"'></div>";
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
