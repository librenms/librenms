<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       http://librenms.org
 * @copyright  2019 LibreNMS
 * @author     LibreNMS Contributors
*/

print_optionbar_start();
echo "<span style='font-weight: bold;'>Modules</span>";
print_optionbar_end();

echo '<div class="row">';

$modules = [ 'poller_modules', 'discovery_modules' ];

foreach ($modules as $mod) {
    switch ($mod) {
        case 'poller_modules':
            $header = 'Poller';
            $check_attr = 'poll_';
            $checked_attr = 'poller-module-';
            $check_mod = 'poller_modules';
            $check_modules = $config['poller_modules'];
            break;
        case 'discovery_modules':
            $header = 'Discovery';
            $check_attr = 'discover_';
            $checked_attr = 'discovery-module-';
            $check_mod = 'discovery_modules';
            $check_modules = $config['discovery_modules'];
            break;
    }

    echo '<div class="col-sm-6">';

    print_optionbar_start();
    echo "<span style='font-weight: bold;'>" . $header . "</span>";
    print_optionbar_end();

    echo '<table class="table table-striped table-condensed">';
    echo '<thead>';
    echo '<th>Module</th>';
    echo '<th>Global</th>';
    echo '<th>OS</th>';
    echo '<th>Device</th>';
    echo '<th>Action</th>';
    echo '</thead>';
    echo '<tbody>';


    ksort($check_modules);
    foreach ($check_modules as $module => $module_status) {
        echo '<tr>';
        echo '<td><strong>' . $module . '</strong></td>';

        echo '<td>';
        if ($module_status == 1) {
            echo '<span class="label label-success">Enabled</span>';
        } else {
            echo '<span class="label label-danger">Disabled</span>';
        }
        echo '</td>';

        echo '<td>';
        if (isset($config['os'][$device['os']]['' . $check_mod . ''][$module])) {
            if ($config['os'][$device['os']]['' . $check_mod . ''][$module]) {
                echo '<span class="label label-success">Enabled</span>';
                $module_status = 1;
            } else {
                echo '<span class="label label-danger">Disabled</span>';
                $module_status = 0;
            }
        } else {
            echo '<span class="label label-default">Unset</span>';
        }
        echo '</td>';

        echo '<td>';
        if (isset($attribs[$check_attr . $module])) {
            if ($attribs[$check_attr . $module]) {
                echo '<span id="' . $checked_attr . $module . '" class="label label-success">Enabled</span>';
                $module_checked = 'checked';
            } else {
                echo '<span id="' . $checked_attr . $module . '" class="label label-danger">Disabled</span>';
                $module_checked = '';
            }
        } else {
            echo '<span id="' . $checked_attr . $module . '" class="label label-default">Unset</span>';
            if ($module_status == 1) {
                $module_checked = 'checked';
            } else {
                $module_checked = '';
            }
        }
        echo '</td>';

        switch ($header) {
            case 'Poller':
                echo '<td><input type="checkbox" style="visibility:hidden;width:100px;" name="poller-module" data-poller_module="' . $module . '" data-device_id="' . $device['device_id'] . '" ' . $module_checked . '></td>';
                break;
            case 'Discovery':
                echo '<td><input type="checkbox" style="visibility:hidden;width:100px;" name="discovery-module" data-discovery_module="' . $module . '" data-device_id="' . $device['device_id'] . '" ' . $module_checked . '></td>';
                break;
        }
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
echo '</div>';
?>

<script>
    $("[name='poller-module']").bootstrapSwitch('offColor', 'danger');
    $('input[name="poller-module"]').on('switchChange.bootstrapSwitch', function (event, state) {
        event.preventDefault();
        var $this = $(this);
        var poller_module = $(this).data("poller_module");
        var device_id = $(this).data("device_id");
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: "poller-module-update", poller_module: poller_module, device_id: device_id, state: state},
            dataType: "html",
            success: function (data) {
                //alert('good');
                if (state) {
                    $('#poller-module-' + poller_module).removeClass('label-danger');
                    $('#poller-module-' + poller_module).addClass('label-success');
                    $('#poller-module-' + poller_module).html('Enabled');
                } else {
                    $('#poller-module-' + poller_module).removeClass('label-success');
                    $('#poller-module-' + poller_module).addClass('label-danger');
                    $('#poller-module-' + poller_module).html('Disabled');
                }
            },
            error: function () {
                //alert('bad');
            }
        });
    });
    $("[name='discovery-module']").bootstrapSwitch('offColor', 'danger');
    $('input[name="discovery-module"]').on('switchChange.bootstrapSwitch', function (event, state) {
        event.preventDefault();
        var $this = $(this);
        var discovery_module = $(this).data("discovery_module");
        var device_id = $(this).data("device_id");
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {
                type: "discovery-module-update",
                discovery_module: discovery_module,
                device_id: device_id,
                state: state
            },
            dataType: "html",
            success: function (data) {
                //alert('good');
                if (state) {
                    $('#discovery-module-' + discovery_module).removeClass('label-danger');
                    $('#discovery-module-' + discovery_module).addClass('label-success');
                    $('#discovery-module-' + discovery_module).html('Enabled');
                } else {
                    $('#discovery-module-' + discovery_module).removeClass('label-success');
                    $('#discovery-module-' + discovery_module).addClass('label-danger');
                    $('#discovery-module-' + discovery_module).html('Disabled');
                }
            },
            error: function () {
                //alert('bad');
            }
        });
    });
</script>
