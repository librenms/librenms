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

if (is_admin() !== false) {
?>

<div class="modal fade bs-example-modal-sm" id="schedule-maintenance" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="Create">Create maintenance</h5>
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
                            <input type="text" class="form-control date" id="start" name="start" value="<?php echo date($config['dateformat']['byminute']); ?>" data-date-format="YYYY-MM-DD HH:mm">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="end" class="col-sm-4 control-label">End: </label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control date" id="end" name="end" value="<?php echo date($config['dateformat']['byminute'], strtotime('+1 hour')); ?>" data-date-format="YYYY-MM-DD HH:mm">
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
                            <button class="btn btn-success" type="submit" name="sched-submit" id="sched-submit" value="save">Schedule maintenance</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
$('#schedule-maintenance').on('hide.bs.modal', function (event) {
    $('#map-tags').data('tagmanager').empty();
    $('#schedule_id').val('');
    $('#title').val('');
    $('#notes').val('');
    $('#start').val('');
    $('#end').val('');
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
            url: "ajax_form.php",
            data: { type: "schedule-maintenance", sub_type: "parse-maintenance", schedule_id: schedule_id },
            dataType: "json",
            success: function(output) {
                var arr = [];
                $.each ( output['targets'], function( key, value ) {
                    arr.push(value);
                });
                $('#map-tags').data('tagmanager').populate(arr);
                $('#title').val(output['title']);
                $('#notes').val(output['notes']);
                $('#start').val(output['start']);
                $('#end').val(output['end']);
            }
        });
    }
});

$('#sched-submit').click('', function(e) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
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

var map_devices = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  remote: {
      url: "ajax_search.php?search=%QUERY&type=device&map=1",
        filter: function (output) {
            return $.map(output, function (item) {
                return {
                    name: item.name,
                };
            });
        },
      wildcard: "%QUERY"
  }
});
var map_groups = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  remote: {
      url: "ajax_search.php?search=%QUERY&type=group&map=1",
        filter: function (output) {
            return $.map(output, function (item) {
                return {
                    name: item.name,
                };
            });
        },
      wildcard: "%QUERY"
  }
});
map_devices.initialize();
map_groups.initialize();
$('#map-stub').typeahead({
    hint: true,
    highlight: true,
    minLength: 1,
    classNames: {
        menu: 'typeahead-left'
    }
},
{
  source: map_devices.ttAdapter(),
  async: true,
  displayKey: 'name',
  valueKey: name,
    templates: {
        suggestion: Handlebars.compile('<p>&nbsp;{{name}}</p>')
    }
},
{
  source: map_groups.ttAdapter(),
  async: true,
  displayKey: 'name',
  valueKey: name,
    templates: {
        suggestion: Handlebars.compile('<p>&nbsp;{{name}}</p>')
    }
});

$(function () {
    $("#start").datetimepicker({
        minDate: '<?php echo date($config['dateformat']['byminute']); ?>'
    });
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
