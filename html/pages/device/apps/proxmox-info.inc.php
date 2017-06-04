<?php

echo '<div class="table-responsive"> <table id="proxmox-info-table" class="table table-condensed table-hover">
    <thead>
        <tr class="tablehead">
            <th data-column-id="vmid" data-width="70px" data-type="numeric" data-identifier="true">VM ID</th>
            <th data-column-id="description">Server Name</th>
            <th data-column-id="vmstatus" data-formatter="vmstatus">Power Status</th>
            <th data-column-id="cluster">Cluster</th>
            <th data-column-id="vmtype">VM Type</th>
            <th data-column-id="vmcpus">CPUs</th>
            <th data-column-id="vmpid"  data-formatter="emptyIfOff">VM PID</th>
            <th data-column-id="vmmem"  data-formatter="units" data-header-align="right" data-align="right">Current Memory</th>
            <th data-column-id="vmmaxmem" data-formatter="units" data-header-align="right" data-align="right">Max Memory</th>
            <th data-column-id="vmmemuse" data-formatter="units" data-header-align="right" data-align="right">Memory Usage</th>
            <th data-column-id="vmdisk" data-formatter="units" data-header-align="right" data-align="right">Current Disk</th>
            <th data-column-id="vmmaxdisk" data-formatter="units" data-header-align="right" data-align="right">Max Disk</th>
            <th data-column-id="vmdiskuse" data-formatter="units" data-header-align="right" data-align="right">Disk Usage</th>
        </tr>
    </thead> </div>
';

echo '</table>';
    
?>
    <script>
        var emptyIfOff = ["vmpid", "vmmem", "vmmemuse", "vmdisk", "vmdiskuse"];
        var percent = ["vmmemuse", "vmdiskuse"];
        
        function printUnits(row, column) {
            if (percent.indexOf(column.id) != -1) {
                return row[column.id] + " %";
            } else {
                if (Number(row[column.id]) > 999) {
                    var gb = Number(row[column.id]) / 1000;
                    return gb.toFixed(1) + " GB";
                } else {
                    return row[column.id] + " MB";
                }
            }
        }
        
        var grid = $("#proxmox-info-table").bootgrid({
            ajax: true,    
            rowCount: [50, 100, 250, -1],
            columnSelection: true,
            formatters: {
                "vmstatus": function (column, row) {
                    var label_type;
                    if (row.vmstatus == "running") {
                        label_type = "success";
                    } else if (row.vmstatus == "stopped") {
                        label_type = "danger";
                    } else {
                        label_type = "default";
                    }
                    return "<span class=\"label label-" + label_type + "\">" + row.vmstatus + "</span>";
                },
                "units": function (column, row) {
                    if (emptyIfOff.indexOf(column.id) != -1) {
                        if (row["vmstatus"] == "running") {
                            return printUnits(row, column);
                        } else {
                            return "";
                        }
                    } else {
                        return printUnits(row, column);
                    }
                    
                },
                "emptyIfOff": function(column, row) {
                    if (row["vmstatus"] == "running") {
                        return row[column.id];
                    } else {
                        return "";
                    }
                }
            },
            post: function () {
                return {
                    id: "proxmox-info",
                    device_id: <?php echo htmlspecialchars($device['device_id']); ?>
                };
            },
            url: "ajax_table.php"
        });
    </script>
<?php

