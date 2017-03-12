<div class="row">
    <div class="col-sm-4">
        <table id="asn" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th data-column-id="asn">ASN</th>
                    <th data-column-id="asname" data-searchable="false">AS Name</th>
                    <th data-column-id="action" data-sortable="false"></th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    var grid = $("#asn").bootgrid({
        ajax: true,
        rowCount: [25,50,100,250,-1],
        post: function ()
        {
            return {
                id:          'as-selection',
            };
        },
        url: "ajax_table.php"
    });
</script>