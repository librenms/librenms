<?php

$no_refresh = TRUE;

$param = array();

if ($vars['action'] == "expunge" && $_SESSION['userlevel'] >= '10')
{
  dbQuery("TRUNCATE TABLE `eventlog`");
  print_message("Event log truncated");
}

$pagetitle[] = "Eventlog";

print_optionbar_start();

?>

<form method="post" action="" class="form-inline" role="form" id="result_form">
    <div class="form-group">
      <label>
        <strong>Device</strong>
      </label>
      <select name="device" id="device" class="form-control input-sm">
        <option value="">All Devices</option>
        <?php
          foreach (get_all_devices() as $hostname)
          {
            echo("<option value='".getidbyname($hostname)."'");

            if (getidbyname($hostname) == $_POST['device']) { echo("selected"); }

            echo(">".$hostname."</option>");
          }
        ?>
      </select>
    </div>
    <button type="submit" class="btn btn-default input-sm">Filter</button>
</form>

<?php

print_optionbar_end();

?>

<table id="eventlog" class="table table-hover table-condensed table-striped">
    <thead>
        <tr>
            <th data-column-id="datetime" data-order="desc">Datetime</th>
            <th data-column-id="hostname">Hostname</th>
            <th data-column-id="type">Type</th>
            <th data-column-id="message">Message</th>
        </tr>
    </thead>
</table>

<script>

var grid = $("#eventlog").bootgrid({
    ajax: true,
    post: function ()
    {
        return {
            id: "eventlog",
            device: '<?php echo htmlspecialchars($_POST['device']); ?>'
        };
    },
    url: "/ajax_table.php"
});

</script>
