<?php

$asn  = $vars['asn'];
$ixid = $vars['ixid'];

?>
<div class="row">
    <div class="col-sm-6">
        <div class="table-responsive">
            <table id="ixpeers" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th data-column-id="peer" data-sortable="false">Peer</th>
                    <th data-column-id="connected" data-sortable="false">Connected</th>
                    <th data-column-id="links" data-sortable="false"></th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
    var grid = $("#ixpeers").bootgrid({
        ajax: true,
        rowCount: [25,50,100,250,-1],
        post: function ()
        {
            return {
                id:          'ixpeers',
                asn:         '<?php echo $asn; ?>',
                ixid:        '<?php echo $ixid; ?>',
            };
        },
        url: "ajax_table.php"
    });
</script>
