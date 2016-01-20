<?php

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

    $common_output[] = "<!-- ".print_r($widget_settings, TRUE)." -->";
    $common_output[] = "<!-- ".print_r($acknowledged, TRUE)." -->";


    $common_output[] = '
<div class="row">
    <div class="col-sm-12">
        <span id="message"></span>
    </div>
</div>
<div class="table-responsive">
    <table id="alerts" class="table table-hover table-condensed alerts">
        <thead>
            <tr>
                <th data-column-id="status" data-formatter="status" data-sortable="false">Status</th>
                <th data-column-id="rule">Rule</th>
                <th data-column-id="details" data-sortable="false">&nbsp;</th>
                <th data-column-id="hostname">Hostname</th>
                <th data-column-id="timestamp">Timestamp</th>
                <th data-column-id="severity">Severity</th>
                <th data-column-id="ack" data-formatter="ack" data-sortable="false">Acknowledge</th>
            </tr>
        </thead>
    </table>
</div>
<script>
var alerts_grid = $("#alerts").bootgrid({
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

