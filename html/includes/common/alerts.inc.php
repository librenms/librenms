<?php

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
            device_id: \'' . $device['device_id'] .'\'
        };
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


