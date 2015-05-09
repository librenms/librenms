<?php
$param = array();

$pagetitle[] = "Alert Log";

            echo('<div class="panel panel-default panel-condensed">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-2">
                            <strong>Alert Log entries</strong>
                        </div>
                        <div class="col-md-2 col-md-offset-8">
                            <div class="pull-right pdf-export"></div>
                        </div>
                    </div>
                </div>
            ');
?>

<table id="alertlog" class="table table-hover table-condensed table-striped">
    <thead>
        <tr>
            <th data-column-id="time_logged" data-order="desc">Time logged</th>
            <th data-column-id="hostname">Device</th>
            <th data-column-id="alert">alert</th>
            <th data-column-id="status" data-sortable="false">Status</th>
        </tr>
    </thead>
</table>
</div>

<script>

var grid = $("#alertlog").bootgrid({
    ajax: true,
    templates: {
        header: "<div id=\"{{ctx.id}}\" class=\"{{css.header}}\"><div class=\"row\">"+
                "<div class=\"col-sm-8 actionBar\"><span class=\"pull-left\">"+
                "<form method=\"post\" action=\"\" class=\"form-inline\" role=\"form\" id=\"result_form\">"+
                "<div class=\"form-group\">"+
                "<label>"+
                "<strong>Device&nbsp;</strong>"+
                "</label>"+
                "<select name=\"device_id\" id=\"device_id\" class=\"form-control input-sm\">"+
                "<option value=\"\">All Devices</option>"+
<?php
    foreach (get_all_devices() as $hostname) {
        $device_id = getidbyname($hostname);
        if (device_permitted($device_id)) {
            echo('"<option value=\"'.$device_id.'\""+');
            if (getidbyname($hostname) == $_POST['device_id']) {
                echo('" selected "+');
            }
           echo('">'.$hostname.'</option>"+');
        }
    }
?>
               "</select>"+
               "</div>"+
               "<button type=\"submit\" class=\"btn btn-default input-sm\">Filter</button>"+
               "</form></span></div>"+
               "<div class=\"col-sm-4 actionBar\"><p class=\"{{css.search}}\"></p><p class=\"{{css.actions}}\"></p></div></div></div>"
    },
    post: function ()
    {
        return {
            id: "alertlog",
            device_id: '<?php echo htmlspecialchars($_POST['device_id']); ?>'
        };
    },
    url: "/ajax_table.php"
}).on("loaded.rs.jquery.bootgrid", function() {

    var results = $("div.infos").text().split(" ");
    low = results[1] -1 ;
    high = results[3];
    max = high - low;
    search = $('.search-field').val();

    $(".pdf-export").html("<a href='pdf.php?report=alert-log&device_id=<?php echo $_POST['device_id'];?>&string="+search+"&results="+max+"&start="+low+"'><img src='images/16/pdf.png' width='16' height='16' alt='Export to pdf'> Export to pdf</a>");
});
</script>
