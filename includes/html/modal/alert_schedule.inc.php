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

if (\Auth::user()->hasGlobalAdmin()) {
    ?>

<div class="modal fade bs-example-modal-sm" id="schedule-maintenance" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="sched-title">Create maintenance</h5>
            </div>
            <div class="modal-body">
                <div id="sched-spinner" style="display: none; width: 100%; height: 200px">
                    <div style="display: flex; justify-content: center; width: 100%; height: 100%">
                        <i class="fa fa-lg fa-spinner fa-spin" style="align-self: center"></i>
                    </div>
                </div>
                <form method="post" role="form" id="sched-form" class="form-horizontal schedule-maintenance-form">
                    <?php echo csrf_field() ?>
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
                        <div class="col-sm-8">
                            <input type="checkbox" id="recurring" name="recurring" data-size="small" data-on-text="Yes" data-off-text="No" onChange="recurring_switch();" value=0 />
                        </div>
                    </div>
                    <div id="norecurringgroup">
                        <div class="form-group">
                            <label for="start" class="col-sm-4 control-label">Start <exp>*</exp>: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="start" name="start" value="" data-date-format="YYYY-MM-DD HH:mm">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end" class="col-sm-4 control-label">End <exp>*</exp>: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="end" name="end" value="" data-date-format="YYYY-MM-DD HH:mm">
                            </div>
                        </div>
                    </div>
                    <div id="recurringgroup" style="display:none;">
                        <div class="form-group">
                            <label for="start_recurring_dt" class="col-sm-4 control-label">Start date <exp>*</exp>: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="start_recurring_dt" name="start_recurring_dt" value="" data-date-format="YYYY-MM-DD">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end_recurring_dt" class="col-sm-4 control-label">End date: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="end_recurring_dt" name="end_recurring_dt" value="" data-date-format="YYYY-MM-DD">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="start_recurring_hr" class="col-sm-4 control-label">Start hour <exp>*</exp>: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="start_recurring_hr" name="start_recurring_hr" value="" data-date-format="HH:mm">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end_recurring_hr" class="col-sm-4 control-label">End hour <exp>*</exp>: </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="end_recurring_hr" name="end_recurring_hr" value="" data-date-format="HH:mm">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="recurring_day" class="col-sm-4 control-label">Only on weekday: </label>
                            <div class="col-sm-8">
                                <div style="float: left;"><label><input type="checkbox" style="width: 20px;" class="form-control" name="recurring_day[]" value="1" />Mo</label></div>
                                <div style="float: left;padding-left: 20px;"><label><input type="checkbox" style="width: 20px;" class="form-control" name="recurring_day[]" value="2" />Tu</label></div>
                                <div style="float: left;padding-left: 20px;"><label><input type="checkbox" style="width: 20px;" class="form-control" name="recurring_day[]" value="3" />We</label></div>
                                <div style="float: left;padding-left: 20px;"><label><input type="checkbox" style="width: 20px;" class="form-control" name="recurring_day[]" value="4" />Th</label></div>
                                <div style="float: left;padding-left: 20px;"><label><input type="checkbox" style="width: 20px;" class="form-control" name="recurring_day[]" value="5" />Fr</label></div>
                                <div style="float: left;padding-left: 20px;"><label><input type="checkbox" style="width: 20px;" class="form-control" name="recurring_day[]" value="6" />Sa</label></div>
                                <div style="float: left;padding-left: 20px;"><label><input type="checkbox" style="width: 20px;" class="form-control" name="recurring_day[]" value="7" />Su</label></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                         <label for='maps' class='col-sm-4 control-label'>Map To <exp>*</exp>: </label>
                        <div class="col-sm-8">
                            <select id="maps" name="maps[]" class="form-control" multiple="multiple"></select>
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
    $('#maps').val(null).trigger('change');
    $('#schedule_id').val('');
    $('#title').val('');
    $('#notes').val('');
    $('#start').val(moment().format('YYYY-MM-DD HH:mm')).data("DateTimePicker").maxDate(false).minDate(moment());
    $('#end').val(moment().add(1, 'hour').format('YYYY-MM-DD HH:mm')).data("DateTimePicker").maxDate(false).minDate(moment());
    var $startRecurringDt = $('#start_recurring_dt');
    $startRecurringDt.val('').data("DateTimePicker").maxDate(false).minDate(moment());
    var $endRecurringDt = $('#end_recurring_dt');
    $endRecurringDt.data("DateTimePicker").date(moment()).maxDate(false).minDate(moment());
    $endRecurringDt.val('');
    $startRecurringDt.data("DateTimePicker").maxDate(false);

    $('#start_recurring_hr').val('').data("DateTimePicker").minDate(false).maxDate(false);
    $('#end_recurring_hr').val('').data("DateTimePicker").minDate(false).maxDate(false);
    $("input[name='recurring_day[]']").prop('checked', false);
    $("#recurring").bootstrapSwitch('state', false);
    $('#recurring').val(0);
    $('#norecurringgroup').show();
    $('#recurringgroup').hide();
    $('#schedulemodal-alert').remove();
});

$('#schedule-maintenance').on('show.bs.modal', function (event) {
    var schedule_id = $('#schedule_id').val();
    if (schedule_id > 0) {
        $('#sched-title').text('<?php echo __('Edit Schedule'); ?>');
        $('#sched-form').hide();
        $('#sched-spinner').show();
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: { type: "schedule-maintenance", sub_type: "parse-maintenance", schedule_id: schedule_id },
            dataType: "json",
            success: function(output) {
                var maps = $('#maps');
                var selected = [];
                $.each ( output['targets'], function( key, item ) {
                    // create options if they don't exist
                    if (maps.find("option[value='" + item.id + "']").length === 0) {
                        var newOption = new Option(item.text, item.id, true, true);
                        maps.append(newOption);
                    }
                    selected.push(item.id);
                });
                maps.val(selected).trigger('change');

                $('#title').val(output['title']);
                $('#notes').val(output['notes']);
                if (output['recurring'] == 0){
                    var start = $('#start').data("DateTimePicker");
                    if (output['start']) {
                        start.minDate(moment(output['start']));
                    }
                    start.date(moment(output['start']));
                    $('#end').data("DateTimePicker").date(moment(output['end']));

                    $('#norecurringgroup').show();
                    $('#recurringgroup').hide();

                    $('#start_recurring_dt').val('');
                    $('#end_recurring_dt').val('');
                    $('#start_recurring_hr').val('');
                    $('#end_recurring_hr').val('');
                    $("input[name='recurring_day[]']").prop('checked', false);
                    $("#recurring").bootstrapSwitch('state', false);
                    $('#recurring').val(0);
                }else{
                    $('#sched-title').text('<?php echo __('Create Schedule'); ?>');
                    var start_recurring_dt = $('#start_recurring_dt').data("DateTimePicker");
                    if (output['start_recurring_dt']) {
                        start_recurring_dt.minDate(output['start_recurring_dt']);
                    }
                    start_recurring_dt.date(output['start_recurring_dt']);
                    $('#end_recurring_dt').data("DateTimePicker").date(output['end_recurring_dt']);

                    var start_recurring_hr = $('#start_recurring_hr').data("DateTimePicker");
                    if (output['start_recurring_dt']) {
                        start_recurring_dt.minDate(output['start_recurring_dt']);
                    }
                    start_recurring_hr.date(output['start_recurring_hr']);
                    $('#end_recurring_hr').data("DateTimePicker").date(output['end_recurring_hr']);

                    $("#recurring").bootstrapSwitch('state', true);
                    $('#recurring').val(1);
                    var daysofweek = {"Mo": 1, "Tu": 2, "We": 3, "Th": 4, "Fr": 5, "Sa": 6, "Su": 7};
                    var arrayrecdayupd = output['recurring_day'];
                    $("input[name='recurring_day[]']").prop('checked', false);
                    $.each(arrayrecdayupd, function(indexdayup, recdayupd){
                        $("input[name='recurring_day[]'][value="+daysofweek[recdayupd]+"]").prop('checked', true);
                    });

                    $('#norecurringgroup').hide();
                    $('#recurringgroup').show();

                    $('#start').val('');
                    $('#end').val('');
                }

                // show
                $('#sched-spinner').hide();
                $('#sched-form').show();
            },
            error: function(){
                $("#schedule-maintenance").modal('hide');
                toastr.error('<?php echo __('Failed to load schedule'); ?>');
            }
        });
    } else {
        $('#sched-title').text('<?php echo __('Create Schedule'); ?>');
        $('#sched-spinner').hide();
        $('#sched-form').show();
    }
});

function recurring_switch() {
    if (document.getElementById("recurring").checked){
        $('#norecurringgroup').hide();
        $('#recurringgroup').show();
        $('#recurring').val(1);
    }else{
        $('#norecurringgroup').show();
        $('#recurringgroup').hide();
        $('#recurring').val(0);
    }
}

$('#sched-submit').on("click", function(e) {
    e.preventDefault();
    // parse start/end to ISO8601
    var formData = $('form.schedule-maintenance-form').serializeArray();
    formData.find(input => input.name === 'start').value = $('#start').data("DateTimePicker").date().format();
    formData.find(input => input.name === 'end').value = $('#end').data("DateTimePicker").date().format();
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: formData,
        dataType: "json",
        success: function(data){
            if(data.status == 'ok') {
                $("#message").html('<div id="schedulemsg" class="alert alert-info"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+data.message+'</div>');
                window.setTimeout(function() { $('#schedulemsg').fadeOut().slideUp(); } , 5000);
                $("#schedule-maintenance").modal('hide');
                $("#schedulemodal-alert").remove();
                $("#alert-schedule").bootgrid('reload');
            } else {
                $("#response").html('<div id="schedulemodal-alert" class="alert alert-danger">'+data.message+'</div>');
            }
        },
        error: function(){
            $("#response").html('<div id="schedulemodal-alert" class="alert alert-danger">An error occurred.</div>');
        }
    });
});

$("#maps").select2({
    width: '100%',
    placeholder: "Devices, Groups or Locations",
    ajax: {
        url: 'ajax_list.php',
        delay: 250,
        data: function (params) {
            return {
                type: 'devices_groups_locations',
                search: params.term
            };
        }
    }
});

$(function () {
    $("#start").datetimepicker({
        defaultDate: moment(),
        minDate: moment().format('YYYY-MM-DD'),
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
        defaultDate: moment().add(1, 'hour'),
        minDate: moment().format('YYYY-MM-DD'),
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
        defaultDate: moment(),
        minDate: moment().format('YYYY-MM-DD'),
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
        minDate: moment().format('YYYY-MM-DD'),
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
        var $endRecurringDt = $("#end_recurring_dt");
        var val = $endRecurringDt.val();
        $endRecurringDt.data("DateTimePicker").minDate(e.date);
        // work around annoying event interaction
        if (!val) {
            $endRecurringDt.val('');
            $("#start_recurring_dt").data("DateTimePicker").maxDate(false);
        }
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

$("[name='recurring']").bootstrapSwitch();
</script>
    <?php
}
