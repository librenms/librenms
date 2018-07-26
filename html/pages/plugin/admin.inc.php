<?php

use LibreNMS\Authentication\Auth;
use LibreNMS\Plugins;

if (!Auth::user()->hasGlobalAdmin()) {
    include 'includes/error-no-perm.inc.php';
    return;
}

Plugins::sync();
$new_plugins = Plugins::scan('installed');

// Check if we have to toggle enabled / disable a particular module
$plugin_id = filter_input(INPUT_POST, 'plugin_id');
$plugin_active = filter_input(INPUT_POST, 'plugin_active');
if (is_numeric($plugin_id) && is_numeric($plugin_active)) {
    if ($plugin_active == '0') {
        Plugins::activate($plugin_id);
    } elseif ($plugin_active == '1') {
        Plugins::deactivate($plugin_id);
    } else {
        Plugins::deactivate($plugin_id);
    }
}
?>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="pull-left">
            <span style="font-size:20px">System Plugins</span>
        </div>
        <div class="pull-right">
            <div class="fa fa-plug fa-fw fa-lg"></div>
        </div>
    </div>
</div>

<?php
if ($new_plugins > 0) {
    $html = <<<HTML
    <div class="alert alert-warning alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <span style="font-size:20px">Results</span><br>
        We have found {$new_plugins} new plugins that need to be configured and enabled
    </div>
HTML;
    echo $html;
}
?>
<table class="table table-bordered">
    <tr>
        <th>Name</th>
        <th>Action</th>
    </tr>

    <?php
    $list = Plugins::getAll();
    foreach ($list as $plugins) {
        if ($plugins['plugin_active'] == 1) {
            $plugin_colour = 'bg-success';
            $plugin_button = 'danger';
            $plugin_label = 'Disable';
        } else {
            $plugin_colour = 'bg-danger';
            $plugin_button = 'success';
            $plugin_label = 'Enable';
        }

        $html = <<<EOT

    <tr class="$plugin_colour">
        <td>{$plugins['plugin_name']}</td>
        <td>
            <form class="form-inline" role="form" action="" method="post" id="{$plugins['plugin_id']}" name=="{$plugins['plugin_id']}">
                <input type="hidden" name="plugin_id" value="{$plugins['plugin_id']}">
                <input type="hidden" name="plugin_active" value="{$plugins['plugin_active']}">
                <button type="submit" class="btn btn-sm btn-{$plugin_button}">{$plugin_label}</button>
            </form>
        </td>
    </tr>
EOT;
        echo $html;
    }
    ?>    
</table>
