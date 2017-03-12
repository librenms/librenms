<?php

$asn = $vars['asn'];

?>
<div class="row">
     <div class="col-sm-4">
         <div class="table-responsive">
             <table id="ixlist" class="table table-bordered table-striped">
                 <thead>
                     <tr>
                         <th data-column-id="exchange" data-sortable="false">Exchange</th>
                         <th data-column-id="action" data-sortable="false"></th>
                         <th data-column-id="links" data-sortable="false"></th>
                     </tr>
                 </thead>
             </table>
         </div>
     </div>
</div>

<script>
    var grid = $("#ixlist").bootgrid({
        ajax: true,
        rowCount: [25,50,100,250,-1],
        post: function ()
        {
            return {
                id:          'ix-list',
                asn:         '<?php echo $asn; ?>',
            };
        },
        url: "ajax_table.php"
    });
</script>