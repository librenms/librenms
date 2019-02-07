<?php
if (!isset($var['section'])) {
    $vars['section'] = "tnmsnealarms";
}
$pagetitle[] = 'TNMS Alarms';
?>
<h4><i class="fa fa-file-info-text-o"></i> Coriant NE Hardware</h4>
<div class="table-responsive">
   <table id="tnmsnealarms" class="table table-hover table-condensed tnmsnealarms">
       <thead>
          <tr>
              <th data-column-id="neName">Name</th>
              <th data-column-id="neType">Type</th>
              <th data-column-id="alarm_cause">Alarm Cause</th>
              <th data-column-id="alarm_location">Alarm location</th>
              <th data-column-id="neAlarmtimestamp">Alarm Timestamp</th>
          </tr>
       </thead>
   </table>
</div>

<script>
    var grid = $("#tnmsnealarms").bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        post: function()
        {
            return {
                id: "tnmsalarms",
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
