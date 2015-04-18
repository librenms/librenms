<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$pagetitle[] = "Alert Schedule";
$no_refresh = TRUE;
if(is_admin() !== false) {

?>

<div class="modal fade bs-example-modal-sm" id="schedule-maintenance" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="Create">Alert Rules</h5>
            </div>
            <div class="modal-body">
                <form method="post" role="form" id="sched-form" class="form-horizontal schedule-maintenance-form">
                    <input type="hidden" name="schedule_id" id="schedule_id">
                    <input type="hidden" name="type" id="type" value="schedule-maintenance">
                    <input type="hidden" name="sub_type" id="sub_type" value="new-maintenance">
                    <div class="row">
                        <div class="col-md-12">
                            <span id="response"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="title" class="col-sm-4 control-label">Title: </label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="title" name="title" placeholder="Maintenance title">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notes" class="col-sm-4 control-label">Notes: </label>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="notes" name="notes" placeholder="Maintenance notes"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="start" class="col-sm-4 control-label">Start: </label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control date" id="start" name="start" value="<?php echo date('Y-m-d H:i'); ?>" data-date-format="YYYY-MM-DD HH:mm">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="end" class="col-sm-4 control-label">End: </label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control date" id="end" name="end" value="<?php echo date('Y-m-d H:i',strtotime('+1 hour')); ?>" data-date-format="YYYY-MM-DD HH:mm">
                        </div>
                    </div>
                    <div class="form-group">
                         <label for='map-stub' class='col-sm-4 control-label'>Map To: </label>
                        <div class="col-sm-5">
                            <input type='text' id='map-stub' name='map-stub' class='form-control'/>
                        </div>
                        <div class="col-sm-3">
                            <button class="btn btn-primary btn-sm" type="button" name="add-map" id="add-map" value="Add">Add</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <span id="map-tags"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-3">
                            <button class="btn btn-success" type="submit" name="sched-submit" id="sched-submit" value="save">Add maintenance schedule</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <span id="message"></span>
    </div>
</div>

<div class="panel panel-default panel-condensed">
    <div class="table-responsive">
        <table id="alert-schedule" class="table table-condensed">
            <thead>
                <tr>
                    <th data-column-id="title" data-order="asc">Title</th>
                    <th data-column-id="start">Start</th>
                    <th data-column-id="end">End</th>
                    <th data-column-id="actions" data-sortable="false" data-searchable="false" data-formatter="commands">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<script>

var grid = $("#alert-schedule").bootgrid({
    ajax: true,
    formatters: {
        "commands": function(column, row)
        {
            return "<button type=\"button\" class=\"btn btn-xs btn-primary command-edit\" data-toggle='modal' data-target='#schedule-maintenance' data-schedule_id=\"" + row.id + "\"><span class=\"fa fa-pencil\"></span></button> " + 
                "<button type=\"button\" class=\"btn btn-xs btn-danger command-delete\" data-schedule_id=\"" + row.id + "\"><span class=\"fa fa-trash-o\"></span></button>";
        }
    },
    templates: {
        header: "<div id=\"{{ctx.id}}\" class=\"{{css.header}}\"><div class=\"row\">"+
                "<div class=\"col-sm-8 actionBar\"><span class=\"pull-left\">"+
                "<button type=\"button\" class=\"btn btn-primary btn-sm\" data-toggle=\"modal\" data-target=\"#schedule-maintenance\">Schedule maintenance</button>"+
                "</span></div>"+
                "<div class=\"col-sm-4 actionBar\"><p class=\"{{css.search}}\"></p><p class=\"{{css.actions}}\"></p></div></div></div>"
    },
    rowCount: [50,100,250,-1],
    post: function ()
    {
        return {
            id: "alert-schedule",
        };
    },
    url: "/ajax_table.php"
}).on("loaded.rs.jquery.bootgrid", function()
{
    /* Executes after data is loaded and rendered */
    grid.find(".command-edit").on("click", function(e)
    {
        $('#schedule_id').val($(this).data("schedule_id"));
        $("#schedule-maintenance").modal('show');
    }).end().find(".command-delete").on("click", function(e)
    {
        alert("You pressed delete on row: " + $(this).data("row-id"));
    });
});

$('#schedule-maintenance').on('show.bs.modal', function (event) {
    $('#tagmanager').tagmanager();
    var schedule_id = $('#schedule_id').val();
    $('#map-tags').tagmanager({
           strategy: 'array',
           tagFieldName: 'maps[]',
           initialCap: false
    });
    if (schedule_id > 0) {
        $.ajax({
            type: "POST",
            url: "/ajax_form.php",
            data: { type: "schedule-maintenance", sub_type: "parse-maintenance", schedule_id: schedule_id },
            dataType: "json",
            success: function(output) {
                var arr = [];
                $.each ( output['targets'], function( key ) {
                    arr.push(key);
                });
                $('#response').data('tagmanager').populate(arr);
                $('#severity').val(output['severity']).change;
                var extra = $.parseJSON(output['extra']);
                $('#count').val(extra['count']);
            }
        });
    }
});

$('#sched-submit').click('', function(e) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "/ajax_form.php",
        data: $('form.schedule-maintenance-form').serialize(),
        dataType: "json",
        success: function(data){
            if(data.status == 'ok') {
                $("#message").html('<div class="alert alert-info">'+data.message+'</div>');
                $("#schedule-maintenance").modal('hide');
                $("#alert-schedule").bootgrid('reload');
            } else {
                $("#response").html('<div class="alert alert-info">'+data.message+'</div>');
            }
        },
        error: function(){
            $("#response").html('<div class="alert alert-info">An error occurred.</div>');
        }
    });

});

$('#add-map').click('',function (event) {
        $('#map-tags').data('tagmanager').populate([ $('#map-stub').val() ]);
        $('#map-stub').val('');
});

$('#map-stub').typeahead([
    {
      name: 'map_devices',
      remote : '/ajax_search.php?search=%QUERY&type=device&map=1',
      header : '<h5><strong>&nbsp;Devices</strong></h5>',
      template: '{{name}}',
      valueKey:"name",
      engine: Hogan
    },
    {
      name: 'map_groups',
      remote : '/ajax_search.php?search=%QUERY&type=group&map=1',
      header : '<h5><strong>&nbsp;Groups</strong></h5>',
      template: '{{name}}',
      valueKey:"name",
      engine: Hogan
    }
]);

$(function () {
    $("#start").datetimepicker();
    $("#end").datetimepicker();
    $("#start").on("dp.change", function (e) {
        $("#end").data("DateTimePicker").minDate(e.date);
    });
    $("#end").on("dp.change", function (e) {
        $("#start").data("DateTimePicker").maxDate(e.date);
    });
});

</script>

<?php

}

?>
