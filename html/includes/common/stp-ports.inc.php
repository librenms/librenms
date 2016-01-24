<?php

$common_output[] = '
<div class="table-responsive">
    <table id="stp-ports" class="table table-condensed table-hover table-striped">
        <thead>
            <tr>
                <th data-column-id="port">Port</th>
                <th data-column-id="priority">Priority</th>
                <th data-column-id="state">State</th>
                <th data-column-id="enable">Enable</th>
                <th data-column-id="pathCost">Path cost</th>
                <th data-column-id="designatedRoot">Designated root</th>
                <th data-column-id="designatedCost">Designated cost</th>
                <th data-column-id="designatedBridge">Designated bridge</th>
                <th data-column-id="designatedPort">Designated port</th>
                <th data-column-id="forwardTransitions">Fwd trasitions</th>
            </tr>
        </thead>
    <table>
</div>

<script>

var grid = $("#stp-ports").bootgrid( { 
    ajax: true,
    post: function () 
    {
        return {
            id: "stp-ports"
        };
    },
    url: "ajax_table.php"
});

</script>
';
