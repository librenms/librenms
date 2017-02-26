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
                        <label for="title" class="col-sm-4 control-label">Title <exp>*</exp>: </label>
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
                        <label for="recurring" class="col-sm-4 control-label">Recurring <strong class="text-danger">*</strong>: </label>
                        <div class="col-sm-2">
                            <input type="radio" class="form-control" id="recurring0" name="recurring" value="0" style="width: 20px;" checked="checked"/>
                        </div>
                        <div class="col-sm-2">
                            <label class="col-sm-for="recurring0">No</label>
                        </div>
                        <div class="col-sm-2">
                            <input type="radio" class="form-control" id="recurring1" name="recurring" value="1" style="width: 20px;" />
                        </div>
                        <div class="col-sm-2">
                            <div style="padding-top:10px;"><label for="recurring1">Yes</label></div>
                        </div>
                    </div>
                    <div id="norecurringgroup">
                        <div class="form-group">
                            <label for="start" class="col-sm-4 control-label">Start <exp>*</exp>: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="start" name="start" value="<?php echo date($config['dateformat']['byminute']); ?>" data-date-format="YYYY-MM-DD HH:mm">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end" class="col-sm-4 control-label">End <exp>*</exp>: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="end" name="end" value="<?php echo date($config['dateformat']['byminute'], strtotime('+1 hour')); ?>" data-date-format="YYYY-MM-DD HH:mm">
                            </div>
                        </div>
                    </div>
                    <div id="recurringgroup" style="display:none;">
                        <div class="form-group">
                            <label for="start_recurring_dt" class="col-sm-4 control-label">Start date <exp>*</exp>: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="start_recurring_dt" name="start_recurring_dt" value="<?php echo date($config['dateformat']['start_recurring_dt']); ?>" data-date-format="YYYY-MM-DD">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end_recurring_dt" class="col-sm-4 control-label">End date: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="end_recurring_dt" name="end_recurring_dt" value="<?php echo date($config['dateformat']['end_recurring_dt'], strtotime('+1 hour')); ?>" data-date-format="YYYY-MM-DD">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="start_recurring_hr" class="col-sm-4 control-label">Start hour <exp>*</exp>: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="start_recurring_hr" name="start_recurring_hr" value="<?php echo date($config['dateformat']['start_recurring_hr']); ?>" data-date-format="HH:mm">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end_recurring_hr" class="col-sm-4 control-label">End hour <exp>*</exp>: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="end_recurring_hr" name="end_recurring_hr" value="<?php echo date($config['dateformat']['end_recurring_hr'], strtotime('+1 hour')); ?>" data-date-format="HH:mm">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="recurring_day" class="col-sm-4 control-label">Only on weekday: </label>
                            <div class="col-sm-8">
                                <div style="float: left;"><label><input type="checkbox" style="width: 20px;" class="form-control" id="recurring_day" name="recurring_day[]" value="1" />Mo</label></div>
                                <div style="float: left;padding-left: 20px;"><label><input type="checkbox" style="width: 20px;" class="form-control" id="recurring_day" name="recurring_day[]" value="2" />Tu</label></div>
                                <div style="float: left;padding-left: 20px;"><label><input type="checkbox" style="width: 20px;" class="form-control" id="recurring_day" name="recurring_day[]" value="3" />We</label></div>
                                <div style="float: left;padding-left: 20px;"><label><input type="checkbox" style="width: 20px;" class="form-control" id="recurring_day" name="recurring_day[]" value="4" />Th</label></div>
                                <div style="float: left;padding-left: 20px;"><label><input type="checkbox" style="width: 20px;" class="form-control" id="recurring_day" name="recurring_day[]" value="5" />Fr</label></div>
                                <div style="float: left;padding-left: 20px;"><label><input type="checkbox" style="width: 20px;" class="form-control" id="recurring_day" name="recurring_day[]" value="6" />Sa</label></div>
                                <div style="float: left;padding-left: 20px;"><label><input type="checkbox" style="width: 20px;" class="form-control" id="recurring_day" name="recurring_day[]" value="0" />Su</label></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                         <label for='map-stub' class='col-sm-4 control-label'>Map To <exp>*</exp>: </label>
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
    $('#recurring').val('');
    $('#start').val('');
    $('#end').val('');
    $('#start_recurring_dt').val('');
    $('#end_recurring_dt').val('');
    $('#start_recurring_hr').val('');
    $('#end_recurring_hr').val('');
    $("#recurring0").prop("checked", true);
    $('#recurring_day').prop('checked', false);
    $('#norecurringgroup').show();
    $('#recurringgroup').hide();
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
                if (output['recurring'] == 0){
                    $('#start').val(output['start']);
                    $('#end').val(output['end']);                    

                    $('#norecurringgroup').show();
                    $('#recurringgroup').hide();
                    
                    $('#start_recurring_dt').val('');
                    $('#end_recurring_dt').val('');
                    $('#start_recurring_hr').val('');
                    $('#end_recurring_hr').val('');
                    $("#recurring0").prop("checked", true);
                    $('#recurring_day').prop('checked', false);
                }else{
                    
                    $('#start_recurring_dt').val(output['start_recurring_dt']);
                    $('#end_recurring_dt').val(output['end_recurring_dt']);
                    $('#start_recurring_hr').val(output['start_recurring_hr']);
                    $('#end_recurring_hr').val(output['end_recurring_hr']);
                    $("#recurring1").prop("checked", true);
                    
                    var recdayupd = output['recurring_day'];
                    if (recdayupd != ''){
                        var arrayrecdayupd = recdayupd.split(',');
                        $.each(arrayrecdayupd, function(indexcheckedday, checkedday){
                            $("input[name='recurring_day[]'][value="+checkedday+"]").prop('checked', true);
                        });
                    }else{
                        $('#recurring_day').prop('checked', false);                        
                    }
                    
                    $('#norecurringgroup').hide();
                    $('#recurringgroup').show();
                    
                    $('#start').val('');
                    $('#end').val('');
                }

            }
        });
    }
});

$('#sched-form input[name=recurring]').on('change', function() {
    var isrecurring = $('input[name=recurring]:checked', '#sched-form').val(); 
    if (isrecurring == 1){
        $('#norecurringgroup').hide();
        $('#recurringgroup').show();
    }else{
        $('#norecurringgroup').show();
        $('#recurringgroup').hide();
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
        minDate: '<?php echo date($config['dateformat']['byminute']); ?>',
        icons: {
            time: 'fa fa-clock-o',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-calendar-check-o',
            clear: 'fa fa-trash-o',
            close: 'fa fa-close'
        }
    });
    $("#end").datetimepicker({
        icons: {
            time: 'fa fa-clock-o',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-calendar-check-o',
            clear: 'fa fa-trash-o',
            close: 'fa fa-close'
        }
    });
    $("#start").on("dp.change", function (e) {
        $("#end").data("DateTimePicker").minDate(e.date);
    });
    $("#end").on("dp.change", function (e) {
        $("#start").data("DateTimePicker").maxDate(e.date);
    });
    $("#start_recurring_dt").datetimepicker({
        minDate: '<?php echo date($config['dateformat']['byminute']); ?>',
        icons: {
            time: 'fa fa-clock-o',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-calendar-check-o',
            clear: 'fa fa-trash-o',
            close: 'fa fa-close'
        }
    });
    $("#end_recurring_dt").datetimepicker({
        icons: {
            time: 'fa fa-clock-o',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-calendar-check-o',
            clear: 'fa fa-trash-o',
            close: 'fa fa-close'
        }
    });
    $("#start_recurring_dt").on("dp.change", function (e) {
        $("#end_recurring_dt").data("DateTimePicker").minDate(e.date);
    });
    $("#end_recurring_dt").on("dp.change", function (e) {
        $("#start_recurring_dt").data("DateTimePicker").maxDate(e.date);
    });
    $("#start_recurring_hr").datetimepicker({
        icons: {
            time: 'fa fa-clock-o',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-calendar-check-o',
            clear: 'fa fa-trash-o',
            close: 'fa fa-close'
        }
    });
    $("#end_recurring_hr").datetimepicker({
        icons: {
            time: 'fa fa-clock-o',
            date: 'fa fa-calendar',
            up: 'fa fa-chevron-up',
            down: 'fa fa-chevron-down',
            previous: 'fa fa-chevron-left',
            next: 'fa fa-chevron-right',
            today: 'fa fa-calendar-check-o',
            clear: 'fa fa-trash-o',
            close: 'fa fa-close'
        }
    });
    $("#start_recurring_hr").on("dp.change", function (e) {
        $("#end_recurring_hr").data("DateTimePicker").minDate(e.date);
    });
    $("#end_recurring_hr").on("dp.change", function (e) {
        $("#start_recurring_hr").data("DateTimePicker").maxDate(e.date);
    });
});

</script>
<?php
}
