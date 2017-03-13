<?php

$asn    = $vars['asn'];
$ixid   = $vars['ixid'];
$status = $vars['status'];

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
        templates: {
            header: 
                "<div id=\"{{ctx.id}}\" class=\"{{css.header}}\"><div class=\"row\">"+
                "<div class=\"col-sm-6 actionBar\"><span class=\"pull-left\">"+
                "<form method=\"post\" action=\"\" class=\"form-inline\" role=\"form\">"+
                "<div class=\"form-group\">"+
                "<select name=\"status\" id=\"status\" class=\"form-control input-sm\">"+
                "<option value=\"all\">All</option>"+
                "<option value=\"connected\">Connected</option>"+
                "<option value=\"unconnected\">Not connected</option>"+
                "</select>"+
                "<button type=\"submit\" class=\"btn btn-default input-sm\">Search</button>"+
                "</div></form></span></div>"+
                "<div class=\"col-sm-6 actionBar\"><span class=\"{{css.search}}\"></span> <span class=\"{{css.actions}}\"></span></div></div></div>"
        },
        post: function ()
        {
            return {
                id:          'ix-peers',
                asn:         '<?php echo $asn; ?>',
                ixid:        '<?php echo $ixid; ?>',
                status:      '<?php echo $status; ?>',
            };
        },
        url: "ajax_table.php"
    });
</script>
