<?php
$pagetitle[] = "Health :: Processor";
?>

<div class="panel panel-default panel-condensed">
    <div class="panel-heading">
        <strong>Health :: Processor</strong>
        <div class="pull-right">
            <?php echo $displayoptions; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table id="processor" class="table table-hover table-condensed processor">
            <thead>
                <tr>
                    <th data-column-id="hostname">Device</th>
                    <th data-column-id="processor_descr">Processor</th>
                    <th data-column-id="graph" data-sortable="false" data-searchable="false"></th>
                    <th data-column-id="processor_usage" data-searchable="false">Usage</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<script>
    var grid = $("#processor").bootgrid({
        ajax: true,
        rowCount: [50,100,250,-1],
        post: function ()
        {
            return {
                id: "processor",
                view: '<?php echo $vars['view']; ?>'
            };
        },
        url: "ajax_table.php"
    });
</script>
