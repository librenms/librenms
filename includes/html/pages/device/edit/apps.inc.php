<h3> Applications </h3>
<?php

use LibreNMS\Config;

// Load our list of available applications
$applications = [];
foreach (glob(Config::get('install_dir') . '/includes/polling/applications/*.inc.php') as $file) {
    $name = basename($file, '.inc.php');
    $applications[$name] = $name;
}

// Generate a list of enabled apps with a value of whether they are discovered or not
$enabled_apps = array_reduce(dbFetchRows(
    'SELECT `app_type`,`discovered` FROM `applications` WHERE `device_id`=? ORDER BY `app_type`',
    [$device['device_id']]
), function ($result, $app) {
    $result[$app['app_type']] = $app['discovered'];

    return $result;
}, []);

echo '<ul class="list-group row">';
foreach ($applications as $app) {
    $modifiers = '';
    $app_text = \LibreNMS\Util\StringHelpers::niceCase($app);
    // check if the app exists in the enable apps array and check if it was automatically enabled
    if (isset($enabled_apps[$app])) {
        $modifiers = ' checked';
        if ($enabled_apps[$app]
            && (get_dev_attrib($device, 'poll_applications') || Config::getOsSetting($device['os'], 'poller_modules.applications'))
        ) {
            $app_text .= '<span class="text-success"> (Discovered)</span>';
            $modifiers .= ' disabled';
        }
    }

    echo '<li class="list-group-item col-xs-12 col-md-6 col-lg-4">';
    echo "<input style='visibility:hidden;width:100px;' type='checkbox' name='application' data-size='small'";
    echo " data-application='$app' data-device_id='{$device['device_id']}'$modifiers>";
    echo '<span style="font-size:medium;padding-left:5px;"> ' . $app_text . '</span>';
    echo '</li>';
}

echo '</ul>';
?>

<script>
    $('[name="application"]').bootstrapSwitch('offColor', 'danger');
    $('input[name="application"]').on('switchChange.bootstrapSwitch', function (event, state) {
        event.preventDefault();
        var $this = $(this);
        var application = $this.data("application");
        var device_id = $this.data("device_id");
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: "application-update", application: application, device_id: device_id, state: state},
            success: function(result){
                if (result.status == 0) {
                    toastr.success(result.message);
                } else {
                    toastr.error(result.message);
                    $this.bootstrapSwitch('state', !state, true);
                }
            },
            error: function () {
                toastr.error('Problem with backend');
                $this.bootstrapSwitch('state', !state, true);
            }
        });
    });
</script>
