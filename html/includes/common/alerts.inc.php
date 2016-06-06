<?php

require_once $config['install_dir'].'/includes/device-groups.inc.php';

/* FIXME: is there a central place we can put this? */

$alert_states = array(
    // divined from librenms/alerts.php
    'recovered' => 0,
    'alerted' => 1,
    'acknowledged' => 2,
    'worse' => 3,
    'better' => 4
);

$alert_severities = array(
    // alert_rules.status is enum('ok','warning','critical')
    'ok' => 1,
    'warning' => 2,
    'critical' => 3
);

//if( defined('show_settings') || empty($widget_settings) ) {
if(defined('show_settings')) {
    $current_acknowledged = isset($widget_settings['acknowledged']) ? $widget_settings['acknowledged'] : '';
    $current_severity     = isset($widget_settings['severity']) ? $widget_settings['severity'] : '';
    $current_state        = isset($widget_settings['state']) ? $widget_settings['state'] : '';
    $current_group        = isset($widget_settings['group']) ? $widget_settings['group'] : '';
    $current_proc         = isset($widget_settings['proc']) ? $widget_settings['proc'] : '';

    $common_output[] = '
<form class="form" onsubmit="widget_settings(this); return false;">
  <div class="form-group row">
    <div class="col-sm-4">
      <label for="acknowledged" class="control-label">Show acknowledged alerts: </label>
    </div>
    <div class="col-sm-8">
      <select class="form-control" name="acknowledged">';

    $common_output[] = '<option value=""'.($current_acknowledged == '' ? ' selected' : ' ').'>not filtered</option>';
    $common_output[] = '<option value="1"'.($current_acknowledged == '1' ? ' selected' : ' ').'>show only acknowledged</option>';
    $common_output[] = '<option value="0"'.($current_acknowledged == '0' ? ' selected' : ' ').'>hide acknowledged</option>';

    $common_output[] = '
      </select>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-4">
      <label for="min_severity" class="control-label">Minimum displayed severity:</label>
    </div>
    <div class="col-sm-8">
      <select class="form-control" name="min_severity">
        <option value="">any severity</option>';

    foreach ($alert_severities as $name => $val) {
        $common_output[] = "<option value=\"$val\"".($current_severity == $name || $current_severity == $val ? ' selected' : '').">$name or higher</option>";
    }

    $common_output[] = '
      </select>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-4">
      <label for="state" class="control-label">State:</label>
    </div>
    <div class="col-sm-8">
      <select class="form-control" name="state">';
    $common_output[] = '<option value=""'.($current_state == '' ? ' selected' : '').'>any state</option>';

    foreach ($alert_states as $name => $val) {
        $common_output[] = "<option value=\"$val\"".($current_state == $name || (is_numeric($current_state) && $current_state == $val) ? ' selected' : '').">$name</option>";
    }

    $common_output[] = '
      </select>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-4">
      <label for="group" class="control-label">Device Group:</label>
    </div>
    <div class="col-sm-8">
      <select class="form-control" name="group">';
    $common_output[] = '<option value=""'.($current_group == '' ? ' selected' : '').'>any group</option>';

    $device_groups = GetDeviceGroups();
    $common_output[] = "<!-- ".print_r($device_groups, true)." -->";
    foreach ($device_groups as $group) {
        $group_id = $group['id'];
        $common_output[] = "<option value=\"$group_id\"".(is_numeric($current_group) && $current_group == $group_id ? ' selected' : '').">".$group['name']." - ".$group['description']."</option>";
    }
    $common_output[] = '
      </select>
    </div>
  </div>
  <div class="form-group row">
    <div class="col-sm-4">
      <label for="proc" class="control-label">Show Procedure field: </label>
    </div>
    <div class="col-sm-8">
      <select class="form-control" name="proc">';

    $common_output[] = '<option value="1"'.($current_proc == '1' ? ' selected' : ' ').'>show</option>';
    $common_output[] = '<option value="0"'.($current_proc == '0' ? ' selected' : ' ').'>hide</option>';

    $common_output[] = '
      </select>
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-12">
      <button type="submit" class="btn btn-default">Set</button>
    </div>
  </div>
</form>
';
}
else {
    $device_id    = $device['device_id'];
    $acknowledged = $widget_settings['acknowledged'];
    $state        = $widget_settings['state'];
    $min_severity = $widget_settings['min_severity'];
    $group        = $widget_settings['group'];
    $proc         = $widget_settings['proc'];

    $title = "Alerts";

    // state can be 0 or '', be sure they are treated differently
    if (is_numeric($state)) {
        $state_name = array_search($state, $alert_states);
        $title = "$title ($state_name)";
    }
    elseif ($state) {
        $title = "$title ($state)";
    }

    if (is_numeric($acknowledged)) {
        if ($acknowledged == '0') {
            $title = "Unacknowledged $title";
        }
        elseif ($acknowledged == '1') {
            $title = "Acknowledged $title";
        }
    }

    if (is_numeric($group)) {
        $group_row = dbFetchRow("SELECT * FROM device_groups WHERE id = ?", array($group));
        if ($group_row) {
            $title = "$title for ".$group_row['name'];
        }
    }

    if ($min_severity) {
        $sev_name = $min_severity;
        if (is_numeric($min_severity)) {
            $sev_name = array_search($min_severity, $alert_severities);
            $title = "$title >=$sev_name";
        }
    }

    $widget_settings['title'] = $title;

    $group        = $widget_settings['group'];

    $common_output[] = '
<div class="row">
    <div class="col-sm-12">
        <span id="message"></span>
    </div>
</div>
<div class="table-responsive">
    <table id="alerts_'.$unique_id.'" class="table table-hover table-condensed alerts">
        <thead>
            <tr>
                <th data-column-id="status" data-formatter="status" data-sortable="false">Status</th>
                <th data-column-id="rule">Rule</th>
                <th data-column-id="details" data-sortable="false">&nbsp;</th>
                <th data-column-id="hostname">Hostname</th>
                <th data-column-id="timestamp">Timestamp</th>
                <th data-column-id="severity">Severity</th>
                <th data-column-id="ack" data-formatter="ack" data-sortable="false">Acknowledge</th>';
    if (is_numeric($proc)) {
	if ($proc) { $common_output[] = '<th data-column-id="proc" data-formatter="proc" data-sortable="false">Procedure</th>'; }
    }
    $common_output[] = '
            </tr>
        </thead>
    </table>
</div>
<script>
var alerts_grid = $("#alerts_'.$unique_id.'").bootgrid({
    ajax: true,
    post: function ()
    {
        return {
            id: "alerts",
';

    if (is_numeric($acknowledged)) {
        $common_output[]="acknowledged: '$acknowledged',\n";
    }
    if (isset($state) && $state != '') {
        $common_output[]="state: '$state',\n";
    }
    if (isset($min_severity) && $min_severity != '') {
        $common_output[]="min_severity: '$min_severity',\n";
    }

    if (is_numeric($group)) {
        $common_output[]="group: '$group',\n";
    }
    if (is_numeric($proc)) {
        $common_output[]="proc: '$proc',\n";
    }

    $common_output[]='
            device_id: \'' . $device['device_id'] .'\'
        }
    },
    url: "ajax_table.php",
    formatters: {
        "status": function(column,row) {
            return "<h4><span class=\'label label-"+row.extra+" threeqtr-width\'>" + row.msg + "</span></h4>";
        },
        "ack": function(column,row) {
            return "<button type=\'button\' class=\'btn btn-"+row.ack_col+" btn-sm command-ack-alert\' data-target=\'#ack-alert\' data-state=\'"+row.state+"\' data-alert_id=\'"+row.alert_id+"\' name=\'ack-alert\' id=\'ack-alert\' data-extra=\'"+row.extra+"\'><span class=\'glyphicon glyphicon-"+row.ack_ico+"\'aria-hidden=\'true\'></span></button>";
        },
	"proc": function(column,row) {
		return "<button type=\'button\' class=\'btn command-open-proc\' data-alert_id=\'"+row.alert_id+"\' name=\'open-proc\' id=\'open-proc\'>Open</button>";
	}
    },
    templates: {
    }
}).on("loaded.rs.jquery.bootgrid", function() {
    alerts_grid.find(".incident-toggle").each( function() {
      $(this).parent().addClass(\'incident-toggle-td\');
    }).on("click", function(e) {
      var target = $(this).data("target");
      $(target).collapse(\'toggle\');
      $(this).toggleClass(\'glyphicon-plus glyphicon-minus\');
    });
    alerts_grid.find(".incident").each( function() {
      $(this).parent().addClass(\'col-lg-4 col-md-4 col-sm-4 col-xs-4\');
      $(this).parent().parent().on("mouseenter", function() {
        $(this).find(".incident-toggle").fadeIn(200);
      }).on("mouseleave", function() {
        $(this).find(".incident-toggle").fadeOut(200);
      }).on("click", "td:not(.incident-toggle-td)", function() {
        var target = $(this).parent().find(".incident-toggle").data("target");
        if( $(this).parent().find(".incident-toggle").hasClass(\'glyphicon-plus\') ) {
          $(this).parent().find(".incident-toggle").toggleClass(\'glyphicon-plus glyphicon-minus\');
          $(target).collapse(\'toggle\');
        }
      });
    });
    alerts_grid.find(".command-open-proc").on("click", function(e) {
        e.preventDefault();
        var alert_id = $(this).data("alert_id");
	$.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: { type: "open-proc", alert_id: alert_id },
            success: function(msg){
		if (msg != "ERROR") { window.open(msg); }
		else { $("#message").html(\'<div class="alert alert-info">Procedure link does not seem to be valid, please check the rule.</div>\'); }
            },
            error: function(){
                 $("#message").html(\'<div class="alert alert-info">An error occurred opening procedure for this alert. Does the procedure link was configured  ?</div>\');
            }
        });
    });
    alerts_grid.find(".command-ack-alert").on("click", function(e) {
        e.preventDefault();
        var alert_id = $(this).data("alert_id");
        var state = $(this).data("state");
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: { type: "ack-alert", alert_id: alert_id, state: state },
            success: function(msg){
                $("#message").html(\'<div class="alert alert-info">\'+msg+\'</div>\');
                if(msg.indexOf("ERROR:") <= -1) {
                    location.reload();
                }
            },
            error: function(){
                 $("#message").html(\'<div class="alert alert-info">An error occurred acking this alert.</div>\');
            }
        });
    });
});
</script>';
}

