<h3> Modules </h3>
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
        <th>Device</th>
        <th></th>
      </tr>
<?php

foreach ($config['poller_modules'] as $module => $module_status) {
    echo('
      <tr>
        <td><strong>'.$module.'</strong></td>
        <td>
        ');

    if($module_status == 1) {
        echo('<span class="text-success">Enabled</span>');
    }
    else {
        echo('<span class="text-danger">Disabled</span>');
    }

    echo('
        </td>
        <td>
        ');

    if (isset($attribs['poll_'.$module])) {
        if ($attribs['poll_'.$module]) {
            echo('<span id="poller-module-'.$module.'" class="text-success">Enabled</span>');
            $module_checked = 'checked';
        }
        else {
            echo('<span id="poller-module-'.$module.'"class="text-danger">Disabled</span>');
            $module_checked = '';
        }
    }
    else {
        if($module_status == 1) {
            echo('<span id="poller-module-'.$module.'"class="text-success">Enabled</span>');
            $module_checked = 'checked';
        }
        else {
            echo('<span id="poller-module-'.$module.'"class="text-danger">Disabled</span>');
            $module_checked = '';
        }
    }

    echo('
       </td>
       <td>
       ');

    echo('<input type="checkbox" name="poller-module" data-poller_module="'.$module.'" data-device_id="'.$device['device_id'].'" '.$module_checked.'>');

    echo('
       </td>
     </tr>
     ');
}

?>

    </table>
  </div>
  <div class="col-sm-6">
    <table class="table table-striped">
      <tr>
        <th>Module</th>
        <th>Global</th>
        <th>Device</th>
        <th></th>
      </tr>

<?php

foreach ($config['discovery_modules'] as $module => $module_status) {
    echo('
      <tr>
        <td>
          <strong>'.$module.'</strong>
        </td>
        <td>
        ');

    if($module_status == 1) {
        echo('<span class="text-success">Enabled</span>');
    }
    else {
        echo('<span class="text-danger">Disabled</span>');
    }

    echo('
        </td>
        <td>');

    if (isset($attribs['discover_'.$module])) {
        if($attribs['discover_'.$module]) {
            echo('<span id="discovery-module-'.$module.'" class="text-success">Enabled</span>');
            $module_checked = 'checked';
        }
        else {
            echo('<span id="discovery-module-'.$module.'" class="text-danger">Disabled</span>');
            $module_checked = '';
        }
    }
    else {
        if($module_status == 1) {
            echo('<span id="discovery-module-'.$module.'" class="text-success">Enabled</span>');
            $module_checked = 'checked';
        }
        else {
            echo('<span id="discovery-module-'.$module.'" class="text-danger">Disabled</span>');
            $module_checked = '';
        }
    }

    echo('
        </td>
        <td>');

    echo('<input type="checkbox" name="discovery-module" data-discovery_module="'.$module.'" data-device_id="'.$device['device_id'].'" '.$module_checked.'>');

    echo('
        </td>
      </tr>');

}
echo('
    </table>
  </div>
');

?>

<script>
  $("[name='poller-module']").bootstrapSwitch('offColor','danger');
  $('input[name="poller-module"]').on('switchChange.bootstrapSwitch',  function(event, state) {
    event.preventDefault();
    var $this = $(this);
    var poller_module = $(this).data("poller_module");
    var device_id = $(this).data("device_id");
    $.ajax({
      type: 'POST',
      url: 'ajax_form.php',
      data: { type: "poller-module-update", poller_module: poller_module, device_id: device_id, state: state},
      dataType: "html",
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
      },
      error:function(){
        //alert('bad');
      }
    });
  });
  $("[name='discovery-module']").bootstrapSwitch('offColor','danger');
  $('input[name="discovery-module"]').on('switchChange.bootstrapSwitch',  function(event, state) {
    event.preventDefault();
    var $this = $(this);
    var discovery_module = $(this).data("discovery_module");
    var device_id = $(this).data("device_id");
    $.ajax({
      type: 'POST',
      url: 'ajax_form.php',
      data: { type: "discovery-module-update", discovery_module: discovery_module, device_id: device_id, state: state},
      dataType: "html",
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
      },
      error:function(){
        //alert('bad');
      }
    });
  });
</script>
