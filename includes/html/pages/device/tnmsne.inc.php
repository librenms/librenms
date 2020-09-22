<?php
if (! isset($var['section'])) {
    $vars['section'] = 'tnmsne';
}
$pagetitle[] = 'Hardware';
?>
<h4><i class="fa fa-file-info-text-o"></i> Coriant NE Hardware</h4>
<div class="table-responsive">
   <table id="tnmsne" class="table table-hover table-condensed tnmsne">
       <thead>
          <tr>
              <th data-column-id="neName">Name</th>
              <th data-column-id="neLocation">Location</th>
              <th data-column-id="neType">Type</th>
              <th data-column-id="neOpMode">Operation Mode</th>
              <th data-column-id="neAlarm">Alarm</th>
              <th data-column-id="neOpState">State</th>
          </tr>
       </thead>
   </table>
</div>

<script>
    var grid = $("#tnmsne").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function()
        {
            return {
                id: "tnmsneinfo",
                device_id: '<?php echo htmlspecialchars($device['device_id']); ?>',
            };
        },
        url: "ajax_table.php",
        formatters: {
        },
        templates: {
        }
    });
</script>
