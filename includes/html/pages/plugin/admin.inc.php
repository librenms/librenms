<?php

if (Auth::user()->hasGlobalAdmin()) {
    // Scan for new plugins and add to the database
    $new_plugins = scan_new_plugins();
    $removed_plugins = scan_removed_plugins();

    // Check if we have to toggle enabled / disable a particular module
    $plugin_id = $_POST['plugin_id'];
    $plugin_active = $_POST['plugin_active'];
    if (is_numeric($plugin_id) && is_numeric($plugin_active)) {
        if ($plugin_active == '0') {
            $plugin_active = 1;
        } elseif ($plugin_active == '1') {
            $plugin_active = 0;
        } else {
            $plugin_active = 0;
        }

        if (dbUpdate(['plugin_active' => $plugin_active], 'plugins', '`plugin_id` = ?', [$plugin_id])) {
            echo '
<script type="text/javascript">
$.ajax({
    url: "",
    context: document.body,
    success: function(s,x){
        $(this).html(s);
    }
});
</script>
';
        }
    }//end if?>

<div class="panel panel-default panel-condensed">
  <div class="panel-heading">
    <strong>System plugins</strong>
  </div>
    <?php
    if ($new_plugins > 0) {
        echo '<div class="panel-body">
    <div class="alert alert-warning">
      We have found ' . $new_plugins . ' new plugins that need to be configured and enabled
    </div>
  </div>';
    }
    if ($removed_plugins > 0) {
        echo '<div class="panel-body">
    <div class="alert alert-warning">
      We have found ' . $removed_plugins . ' removed plugins
    </div>
  </div>';
    } ?>
  <table class="table table-condensed">
    <tr>
      <th>Name</th>
      <th>Action</th>
    </tr>

    <?php
    foreach (dbFetchRows('SELECT * FROM plugins') as $plugins) {
        if ($plugins['plugin_active'] == 1) {
            $plugin_colour = 'bg-success';
            $plugin_button = 'danger';
            $plugin_label = 'Disable';
        } else {
            $plugin_colour = 'bg-danger';
            $plugin_button = 'success';
            $plugin_label = 'Enable';
        }

        echo '<tr class="' . $plugin_colour . '">
            <td>
              ' . $plugins['plugin_name'] . '
            </td>
            <td>
              <form class="form-inline" role="form" action="" method="post" id="' . $plugins['plugin_id'] . '" name=="' . $plugins['plugin_id'] . '">
                ' . csrf_field() . '
                <input type="hidden" name="plugin_id" value="' . $plugins['plugin_id'] . '">
                <input type="hidden" name="plugin_active" value="' . $plugins['plugin_active'] . '">
                <button type="submit" class="btn btn-sm btn-' . $plugin_button . '">' . $plugin_label . '</button>
              </form>
            </td>
          </tr>';
    }//end foreach
    ?>
  </table>
</div>

    <?php
} else {
        include 'includes/html/error-no-perm.inc.php';
    }//end if
