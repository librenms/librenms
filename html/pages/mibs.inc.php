<h4><i class="fa fa-file-text-o"></i> Loaded MIB definitions</h4>
<div class="table-responsive">
    <table id="mibs" class="table table-hover table-condensed mibs">
        <thead>
            <tr>
                <th data-column-id="module">Module</th>
                <th data-column-id="mib">MIB</th>
                <th data-column-id="object_type">Object Type</th>
                <th data-column-id="oid">Object Id</th>
                <th data-column-id="syntax">Syntax</th>
                <th data-column-id="description">Description</th>
                <th data-column-id="max_access">Maximum Access</th>
                <th data-column-id="status">Status</th>
            </tr>
        </thead>
    </table>
</div>

<script>
    var grid = $("#mibs").bootgrid({
        ajax: true,
        rowCount: [50,100,250,-1],
        post: function ()
        {
            return {
                id: "mibs",
                view: '<?php echo $vars['view']; ?>'
            };
        },
        url: "/ajax_table.php",
        formatters: {
        },
        templates: {
        }
    });
</script>
