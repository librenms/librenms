<?php

/* Copyright (C) 2015 Daniel Preussker <f0o@devilcode.org>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * Global Settings
 * @author f0o <f0o@devilcode.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Page
 */

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <span id="message"></span>
        </div>
    </div>
</div>

<?php

if (isset($vars['sub'])) {

    if (file_exists("pages/settings/".mres($vars['sub']).".inc.php")) {
        require_once "pages/settings/".mres($vars['sub']).".inc.php";
    } else {
        print_error("This settings page doesn't exist, please go to the main settings page");
    }

} else {

?>

<div class="container-fluid">
    <div class="row">
<?php
foreach (dbFetchRows("SELECT `config_group` FROM `config` GROUP BY `config_group`") as $sub_page) {
    $sub_page = $sub_page['config_group'];
?>
        <div class="col-lg-1 col-md-1 col-sm-1 col-xs-1">
            <a class="btn btn-primary" href="<?php echo(generate_url(array('page'=>'settings','sub'=>$sub_page))); ?>"><?php echo ucfirst($sub_page); ?></a>
        </div>
<?php
}
?>
    </div>
</div>

<?php

/**
 * Array-To-Table
 * @param array $a N-Dimensional, Associative Array
 * @return string
 */

function a2t($a) {
	$r = "<table class='table table-condensed table-hover'><tbody>";
	foreach( $a as $k=>$v ) {
		if( !empty($v) ) {
			$r .= "<tr><td class='col-md-2'><i><b>".$k."</b></i></td><td class='col-md-10'>".(is_array($v)?a2t($v):"<code>".wordwrap($v,75,"<br/>")."</code>")."</td></tr>";
		}
	}
	$r .= '</tbody></table>';
	return $r;
}
if( $_SESSION['userlevel'] >= 10 ) {
	echo "<div class='table-responsive'>".a2t($config)."</div>";
} else {
	include("includes/error-no-perm.inc.php");
}

if ($_SESSION['userlevel'] >= '10') {

?>

    <div class="modal fade" id="new-config-form" role="dialog" aria-hidden="true" title="Create new config item">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-body">
            <form role="form" class="new_config_form">
              <div class="form-group">
                <label for="new_conf_type">Config type</label>
                <select class="form-control" name="new_conf_type" id="new_conf_type" onChange="showInput();">
                  <option>Single</option>
                  <option>Standard Array</option>
                  <option>Multi Array</option>
                  <option>Single Array</option>
                </select>
              </div>
              <div class="form-group">
                <label for="new_conf_name">Config name</label>
                <input type="text" class="form-control" name="new_conf_name" id="new_conf_name" placeholder="Enter the config name">
              </div>

              <div class="form-group" id="single_value">
                <label for="new_conf_value">Config value</label>
                <input type="text" class="form-control" name="new_conf_single_value" id="new_conf_single_value" placeholder="Enter the config value">
              </div>
              <div class="form-group" id="multi_value">
                <label for="new_conf_value">Config value</label>
                <textarea class="form-control" rows="3" name="new_conf_multi_value" id="new_conf_multi_value" placeholder="Enter the config value, each item must be on a new line"></textarea>
              </div>
<script>
  function showInput()
  {
    confType = $("#new_conf_type").val();
    if(confType == 'Single' || confType == 'Single Array')
    {
      $('#multi_value').hide();
      $('#single_value').show();
    }
    else if(confType == 'Standard Array' || confType == 'Multi Array')
    {
      $('#single_value').hide();
      $('#multi_value').show();
    }
  }
$('#multi_value').toggle();
</script>
              <div class="form-group">
                <label for="new_conf_desc">Description</label>
                <input type="text" class="form-control" name="new_conf_desc" id="new_conf_desc" placeholder="Enter the description of this config item">
              </div>
            </div>
          </form>
            <div class="modal-footer">
              <button class="btn btn-success" id="submit">Add config</button>
              <a href="#" class="btn" data-dismiss="modal">Cancel</a>
            </div>
        </div>
      </div>
    </div>

<?php

  $found=0;
  echo('
      <div class="row">
        <div class="col-md-12">
          <span id="thanks"></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-9">
          <h4>System Settings</h4>
        </div>
        <div class="col-md-3">
          <div class="btn-toolbar" role="toolbar">
            <div class="btn-group">
              <button type="button" name="options" id="expand" class="btn btn-xs btn-default"> Expand
              <button type="button" name="options" id="collapse" class="btn btn-xs btn-default"> Collapse
              <button type="button" name="new_config" id="new_config_item" data-toggle="modal" data-target="#new-config-form" class="btn btn-xs btn-default"> New config item
            </div>
          </div>
        </div>
      </div>
      <form class="form-horizontal" role="form" action="" method="post">
      <div class="panel-group" id="accordion">
');

  foreach (dbFetchRows("SELECT config_id,config_group FROM `config` WHERE config_hidden='0' GROUP BY config_group ORDER BY config_group ASC ,config_group_order DESC") as $group)
  {
    $grp_num = $group['config_group_order'];
    $grp_title = $group['config_group'];
    $found++;
    echo('
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a data-toggle="collapse" data-parent="#accordion" href="#'.$grp_num.'_expand">
                '.$grp_title.'
              </a>
            </h4>
          </div>
          <div id="'.$grp_num.'_expand" class="panel-collapse collapse">
            <div class="panel-body">
');
    foreach (dbFetchRows("SELECT * FROM `config` WHERE config_group='".$group['config_group']."' ORDER BY config_sub_group ASC, config_sub_group_order DESC, config_name ASC") as $cfg)
    {
      $cfg_ids[] = $cfg['config_id'];
      $cfg_disabled = '';
      if($cfg['config_disabled'] == '1')
      {
        $cfg_disabled = 'checked';
      }
      echo('
              <div class="form-group">
                <label for="'.$cfg['config_id'].'" class="col-sm-3">$config[\''.str_replace(",", "']['", $cfg['config_name']).'\'] = </label>
                <div class="col-sm-6 config-response">
                  <input type="input" class="form-control input-sm config-item" name="'.$cfg['config_id'].'" id="'.$cfg['config_id'].'" value="'.stripslashes(htmlspecialchars($cfg['config_value'])).'">
                </div>
                <div class="col-sm-1">
                  <div data-toggle="tooltip" title="'.$cfg['config_desc'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
<script>
$(".toolTip").tooltip();
</script>
                </div>
                <div class="col-sm-2">
                  <input type="checkbox" name="config-status" data-config_id="'.$cfg['config_id'].'" data-off-text="On" data-on-text="Off" data-on-color="danger" '.$cfg_disabled.'>
                </div>
              </div>
');

    }

    echo('
            </div>
          </div>
        </div>
');

  }


    echo('
        <script>
          $("#expand").click(function () {
            $(".collapse").collapse("show");
          });
          $("#collapse").click(function () {
            $(".collapse").collapse("hide");
          });
        </script>
');

  if ($debug)
  {
    echo("<pre>");
    print_r($config);
    echo("</pre>");
  }

?>
<script>

  $(function() {
    $("button#submit").click(function(){
      $.ajax({
        type: "POST",
        url: "form_new_config.php",
        data: $('form.new_config_form').serialize(),
        success: function(msg){
          $("#thanks").html('<div class="alert alert-info">'+msg+'</div>')
          $("#new-config-form").modal('hide');
        },
        error: function(){
          alert("failure");
        }
      });
    });
  });
</script>

<script>
    $( ".config-item" ).blur(function(event) {
      event.preventDefault();
      var config_id = $(this).attr('id');
      var data = $(this).val();
      var $this = $(this);
      $.ajax({
        type: 'POST',
        url: '/ajax_form.php',
        data: { type: "config-item-update", config_id: config_id, data: data},
        dataType: "html",
        success: function(data){
          $this.closest('.config-response').addClass('has-success');
          setTimeout(function(){
            $this.closest('.config-response').removeClass('has-success');
          }, 2000);
        },
        error:function(){
          $(this).closest('.config-response').addClass('has-error');
          setTimeout(function(){
            $this.closest('.config-response').removeClass('has-error');
          }, 2000);
        }
      });
    });
</script>
<script>
  $("[name='config-status']").bootstrapSwitch('offColor','success');
  $('input[name="config-status"]').on('switchChange.bootstrapSwitch',  function(event, state) {
    event.preventDefault();
    var $this = $(this);
    var config_id = $(this).data("config_id");
    $.ajax({
      type: 'POST',
      url: '/ajax_form.php',
      data: { type: "config-item-disable", config_id: config_id, state: state},
      dataType: "html",
      success: function(data){
        //alert('good');
      },
      error:function(){
        //alert('bad');
      }
    });
  });
</script>
<?php

    } else {
        include("includes/error-no-perm.inc.php");
    }
}
?>
