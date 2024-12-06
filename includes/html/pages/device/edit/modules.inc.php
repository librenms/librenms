<div class="row">
  <div class="col-sm-6">
    <strong>Poller Modules</strong>
  </div>
  <div class="col-sm-6">
    <strong>Discovery Modules</strong>
  </div>
</div>
<div class="row">
  <div class="col-sm-6">
    <table class="table table-striped">
      <tr>
        <th>Module</th>
        <th>Global</th>
        <th>OS</th>
        <th>Device</th>
        <th>Override</th>
        <th></th>
      </tr>
<?php

use LibreNMS\Config;
use LibreNMS\Util\Module;

$language = \config('app.locale');
$settings = (include Config::get('install_dir') . '/lang/' . $language . '/settings.php')['settings'];

$attribs = DeviceCache::getPrimary()->getAttribs();
$poller_module_names = $settings['poller_modules'];
$discovery_module_names = $settings['discovery_modules'];

$poller_modules = Config::get('poller_modules');
unset($poller_modules['core']); // core cannot be toggled
ksort($poller_modules);
foreach ($poller_modules as $module => $module_status) {
    $module_name = $poller_module_names[$module]['description'] ?: $module;
    echo '
      <tr>
        <td><strong>' . $module_name . '</strong></td>
        <td>
        ';

    if ($module_status == 1) {
        echo '<span class="text-success">Enabled</span>';
    } else {
        echo '<span class="text-danger">Disabled</span>';
    }

    echo '
        </td>
        <td>';

    if (Config::has("os.{$device['os']}.poller_modules.$module")) {
        if (Config::get("os.{$device['os']}.poller_modules.$module")) {
            echo '<span class="text-success">Enabled</span>';
            $module_status = 1;
        } else {
            echo '<span class="text-danger">Disabled</span>';
            $module_status = 0;
        }
    } else {
        echo '<span>Unset</span>';
    }

    echo '
        </td>
        <td>
        ';

    if (isset($attribs['poll_' . $module])) {
        if ($attribs['poll_' . $module]) {
            echo '<span id="poller-module-' . $module . '" class="text-success">Enabled</span>';
            $module_checked = 'checked';
        } else {
            echo '<span id="poller-module-' . $module . '"class="text-danger">Disabled</span>';
            $module_checked = '';
        }
    } else {
        echo '<span id="poller-module-' . $module . '">Unset</span>';
        if ($module_status == 1) {
            $module_checked = 'checked';
        } else {
            $module_checked = '';
        }
    }

    echo '
       </td>
       <td>
       ';

    echo '<input type="checkbox" style="visibility:hidden;width:100px;" name="poller-module" id="poller-toggle-' . $module . '" ' . $module_checked . '>';

    echo '
       </td>
       <td style="vertical-align: middle">';

    echo '<button type="button" class="btn btn-default tw-mr-1 poller-reset-button" id="poller-reset-button-' . $module . '" style="visibility: ' . (isset($attribs['poll_' . $module]) ? 'visible' : 'hidden') . '" title="Reset device override"><i class="fa fa-lg fa-solid fa-rotate-left"></i></button>';

    $moduleInstance = Module::fromName($module);
    if ($moduleInstance->dataExists(DeviceCache::getPrimary())) {
        echo '<button type="button" class="btn btn-default delete-button-' . $module . '" title="Delete Module Data" data-toggle="modal" data-target="#delete-module-data" data-module="' . $module . '" data-module-name="' . $module_name . '"><i class="fa fa-lg fa-solid fa-trash tw-text-red-600"></button>';
    }

echo '</td>
     </tr>
     ';
}

?>

    </table>
  </div>
  <div class="col-sm-6">
    <table class="table table-striped">
      <tr>
        <th>Module</th>
        <th>Global</th>
        <th>OS</th>
        <th>Device</th>
        <th>Override</th>
        <th></th>
      </tr>

<?php

$discovery_modules = Config::get('discovery_modules');
unset($discovery_modules['core']); // core cannot be toggled
ksort($discovery_modules);
foreach ($discovery_modules as $module => $module_status) {
    $module_name = $discovery_module_names[$module]['description'] ?: $module;
    echo '
      <tr>
        <td>
          <strong>' . $module_name . '</strong>
        </td>
        <td>
        ';

    if ($module_status == 1) {
        echo '<span class="text-success">Enabled</span>';
    } else {
        echo '<span class="text-danger">Disabled</span>';
    }

    echo '
        </td>
        <td>';

    if (Config::has("os.{$device['os']}.discovery_modules.$module")) {
        if (Config::get("os.{$device['os']}.discovery_modules.$module")) {
            echo '<span class="text-success">Enabled</span>';
            $module_status = 1;
        } else {
            echo '<span class="text-danger">Disabled</span>';
            $module_status = 0;
        }
    } else {
        echo '<span>Unset</span>';
    }

    echo '
        </td>
        <td>';

    if (isset($attribs['discover_' . $module])) {
        if ($attribs['discover_' . $module]) {
            echo '<span id="discovery-module-' . $module . '" class="text-success">Enabled</span>';
            $module_checked = 'checked';
        } else {
            echo '<span id="discovery-module-' . $module . '" class="text-danger">Disabled</span>';
            $module_checked = '';
        }
    } else {
        echo '<span id="discovery-module-' . $module . '">Unset</span>';
        if ($module_status == 1) {
            $module_checked = 'checked';
        } else {
            $module_checked = '';
        }
    }

    echo '
        </td>
        <td>';

    echo '<input type="checkbox" style="visibility:hidden;width:100px;" name="discovery-module" id="discovery-toggle-' . $module . '" ' . $module_checked . '>';

    echo '
        </td>
       <td style="vertical-align: middle">';

    echo '<button type="button" class="btn btn-default tw-mr-1 discovery-reset-button" id="discovery-reset-button-' . $module . '" style="visibility: ' . (isset($attribs['discover_' . $module]) ? 'visible' : 'hidden') . '" title="Reset device override"><i class="fa fa-lg fa-solid fa-rotate-left"></i></button>';

    $moduleInstance = Module::fromName($module);
    if ($moduleInstance->dataExists(DeviceCache::getPrimary())) {
        echo '<button type="button" class="btn btn-default delete-button-' . $module . '" title="Delete Module Data" data-toggle="modal" data-target="#delete-module-data" data-module="' . $module . '" data-module-name="' . $module_name . '"><i class="fa fa-lg fa-solid fa-trash tw-text-red-600"></button>';
    }

    echo '</td>
      </tr>';
}
echo '
    </table>
  </div>
<div class="modal fade" id="delete-module-data" tabindex="-1" role="dialog" aria-labelledby="delete-module-dialog-title" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="delete-module-dialog-title">Delete module data for <span class="dialog-module-name">module</span>?</h4>
            </div>
            <div class="modal-body">
                <p>Delete this device&apos;s data for module <span class="dialog-module-name">module</span>?</p>
                <p>Data will not repopulate until discovery and/or polling is run again.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="module-data-delete-button">Delete</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
';

?>

<script>
  $("[name='poller-module']").bootstrapSwitch('offColor','danger');
  $('input[name="poller-module"]').on('switchChange.bootstrapSwitch',  function(event, state) {
    event.preventDefault();
    var poller_module = $(this).attr('id').replace('poller-toggle-', '');
    $.ajax({
      type: 'PUT',
      url: '<?php echo route('device.module.delete', ['device' => $device['device_id'], 'module' => ':module']) ?>'.replace(':module', poller_module),
      data: { polling: state},
      dataType: "json",
      success: function(data){
        //alert('good');
        if(state)
        {
          $('#poller-module-'+poller_module).removeClass('text-danger');
          $('#poller-module-'+poller_module).addClass('text-success');
          $('#poller-module-'+poller_module).html('Enabled');
        }
        else
        {
          $('#poller-module-'+poller_module).removeClass('text-success');
          $('#poller-module-'+poller_module).addClass('text-danger');
          $('#poller-module-'+poller_module).html('Disabled');
        }
        $('#poller-reset-button-' + poller_module).css('visibility', 'visible');
      },
      error:function(){
        //alert('bad');
      }
    });
  });
  $("[name='discovery-module']").bootstrapSwitch('offColor','danger');
  $('input[name="discovery-module"]').on('switchChange.bootstrapSwitch',  function(event, state) {
    event.preventDefault();
    var discovery_module = $(this).attr('id').replace('discovery-toggle-', '');
    $.ajax({
      type: 'PUT',
      url: '<?php echo route('device.module.delete', ['device' => $device['device_id'], 'module' => ':module']) ?>'.replace(':module', discovery_module),
      data: { discovery: state},
      dataType: "json",
      success: function(data){
        //alert('good');
        if(state)
        {
          $('#discovery-module-'+discovery_module).removeClass('text-danger');
          $('#discovery-module-'+discovery_module).addClass('text-success');
          $('#discovery-module-'+discovery_module).html('Enabled');
        }
        else
        {
          $('#discovery-module-'+discovery_module).removeClass('text-success');
          $('#discovery-module-'+discovery_module).addClass('text-danger');
          $('#discovery-module-'+discovery_module).html('Disabled');
        }
        $('#discovery-reset-button-' + discovery_module).css('visibility', 'visible');
      },
      error:function(){
        //alert('bad');
      }
    });
  });
  $('#delete-module-data').on('show.bs.modal', function (event) {
        $('.dialog-module-name').text($(event.relatedTarget).data('module-name'));
        $('#module-data-delete-button').data('module', $(event.relatedTarget).data('module'));
  });
  $('.poller-reset-button').on('click', function (event) {
      var poller_module = $(this).attr('id').replace('poller-reset-button-', '');
      $.ajax({
          type: 'PUT',
          url: '<?php echo route('device.module.delete', ['device' => $device['device_id'], 'module' => ':module']) ?>'.replace(':module', poller_module),
          data: { polling: 'clear'},
          dataType: "json",
          success: function(data){
              $('#poller-toggle-'+poller_module).bootstrapSwitch('state', data.polling, true);
              $('#poller-module-'+poller_module).removeClass('text-danger');
              $('#poller-module-'+poller_module).removeClass('text-success');
              $('#poller-module-'+poller_module).html('Unset');
              $('#poller-reset-button-'+poller_module).css('visibility', 'hidden');
          },
          error:function(){
          }
      });
  });
  $('.discovery-reset-button').on('click', function (event) {
      var discovery_module = $(this).attr('id').replace('discovery-reset-button-', '');
      $.ajax({
          type: 'PUT',
          url: '<?php echo route('device.module.delete', ['device' => $device['device_id'], 'module' => ':module']) ?>'.replace(':module', discovery_module),
          data: { discovery: 'clear'},
          dataType: "json",
          success: function(data){
              $('#discovery-toggle-'+discovery_module).bootstrapSwitch('state', data.discovery, true);
              $('#discovery-module-'+discovery_module).removeClass('text-danger');
              $('#discovery-module-'+discovery_module).removeClass('text-success');
              $('#discovery-module-'+discovery_module).html('Unset');
              $('#discovery-reset-button-'+discovery_module).css('visibility', 'hidden');
          },
          error:function(){
          }
      });
  });
  $('#module-data-delete-button').on('click', function (event) {
      var module = $(this).data('module');
      $.ajax({
          type: 'DELETE',
          url: '<?php echo route('device.module.delete', ['device' => $device['device_id'], 'module' => ':module']) ?>'.replace(':module', module),
          data: {},
          dataType: "json",
          success: function(data){
              console.log('Deleted: ' + data.deleted);
              $('#delete-module-data').modal('hide');
              $('.delete-button-' + module).remove();
          },
          error:function(){
          }
      });
  })
</script>
