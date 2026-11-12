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

use LibreNMS\Enum\MaintenanceBehavior;

$default_behavior = MaintenanceBehavior::fromConfig();
$default_timezone = session('preferences.timezone', config('app.timezone'));
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
                    <div class="row">
                        <div class="col-md-12">
                            <span id="response"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="title" class="col-sm-4 control-label">Title <exp>*</exp> </label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="title" name="title" placeholder="Maintenance title">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notes" class="col-sm-4 control-label">Notes </label>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="notes" name="notes" placeholder="Maintenance notes"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="recurring" class="col-sm-4 control-label">Recurring </label>
                        <div class="col-sm-8">
                            <input type="checkbox" id="recurring" name="recurring" data-size="small" data-on-text="Yes" data-off-text="No" onChange="recurring_switch();" value=0 />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="timezone" class="col-sm-4 control-label">Timezone <exp>*</exp> </label>
                        <div class="col-sm-8">
                            <select id="timezone" name="timezone" class="form-control">
<?php foreach (array_unique([...timezone_identifiers_list(), $default_timezone]) as $timezone): ?>
                                <option value="<?= htmlentities($timezone) ?>" <?= $timezone === $default_timezone ? 'selected' : '' ?>><?= htmlentities($timezone) ?></option>
<?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div id="norecurringgroup">
                        <div class="form-group">
                            <label for="start" class="col-sm-4 control-label">Start <exp>*</exp> </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="start" name="start" value="" data-date-format="YYYY-MM-DD HH:mm">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end" class="col-sm-4 control-label">End <exp>*</exp> </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="end" name="end" value="" data-date-format="YYYY-MM-DD HH:mm">
                            </div>
                        </div>
                    </div>
                    <div id="recurringgroup" style="display:none;">
                        <div class="form-group">
                            <label for="start_recurring_dt" class="col-sm-4 control-label">Start date <exp>*</exp> </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="start_recurring_dt" name="start_recurring_dt" value="" data-date-format="YYYY-MM-DD">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end_recurring_dt" class="col-sm-4 control-label">End date </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="end_recurring_dt" name="end_recurring_dt" value="" data-date-format="YYYY-MM-DD">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="start_recurring_hr" class="col-sm-4 control-label">Start hour <exp>*</exp> </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="start_recurring_hr" name="start_recurring_hr" value="" data-date-format="HH:mm">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end_recurring_hr" class="col-sm-4 control-label">End hour <exp>*</exp> </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control date" id="end_recurring_hr" name="end_recurring_hr" value="" data-date-format="HH:mm">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="recurring_day" class="col-sm-4 control-label">Only on weekday </label>
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
                        <label for='behavior' class='col-sm-4 control-label'>Behavior <exp>*</exp> </label>
                        <div class="col-sm-8">
                            <select id="behavior" name="behavior" class="form-control">
<?php foreach (MaintenanceBehavior::cases() as $behavior): ?>
                                <option value='<?= $behavior->value ?>' <?= $behavior === $default_behavior ? 'selected' : '' ?>>
                                    <?= htmlentities($behavior->descr()) ?>
                                </option>
<?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                         <label for='maps' class='col-sm-4 control-label'>Map To <exp>*</exp> </label>
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
<script src="<?php echo asset('js/RrdGraphJS/moment-timezone-with-data.js'); ?>"></script>
<script>

var scheduleDefaults = {};

// current wall clock time in the selected timezone, for the date pickers which only work with browser-local moments
function scheduleNow(hours) {
    return moment(moment.tz($('#timezone').val()).add(hours || 0, 'hours').format('YYYY-MM-DD HH:mm'), 'YYYY-MM-DD HH:mm');
}

function setScheduleDefault(selector, hours) {
    var $input = $(selector);
    scheduleDefaults[selector] = scheduleNow(hours).format($input.data('date-format'));
    $input.val(scheduleDefaults[selector]);
}

// the selected timezone may be behind the browser, allow dates that are already past locally
function scheduleMinDate() {
    return moment().subtract(1, 'day').startOf('day');
}

$('#schedule-maintenance').on('hide.bs.modal', function (event) {
    $('#maps').val(null).trigger('change');
    $('#schedule_id').val('');
    $('#title').val('');
    $('#notes').val('');
    $('#timezone').val(<?php echo json_encode($default_timezone); ?>);
    setScheduleDefault('#start');
    $('#start').data("DateTimePicker").maxDate(false).minDate(scheduleMinDate());
    setScheduleDefault('#end', 1);
    $('#end').data("DateTimePicker").maxDate(false).minDate(scheduleMinDate());
    var $startRecurringDt = $('#start_recurring_dt');
    $startRecurringDt.val('').data("DateTimePicker").maxDate(false).minDate(scheduleMinDate());
    var $endRecurringDt = $('#end_recurring_dt');
    $endRecurringDt.data("DateTimePicker").date(moment()).maxDate(false).minDate(scheduleMinDate());
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
            type: "GET",
            url: route('alert-schedule.show', {alert_schedule: schedule_id}),
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
                var timezone = $('#timezone');
                if (timezone.find('option').filter(function () { return this.value === output['timezone']; }).length === 0) {
                    timezone.append(new Option(output['timezone'], output['timezone']));
                }
                timezone.val(output['timezone']);
                $('#behavior').find('option[value="'+output['behavior']+'"]').prop('selected', true);
                if (output['recurring'] == 0){
                    $('#start').data("DateTimePicker").date(moment(output['start'], 'YYYY-MM-DD HH:mm'));
                    $('#end').data("DateTimePicker").date(moment(output['end'], 'YYYY-MM-DD HH:mm'));

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

$('#timezone').on('change', function () {
    if ($('#schedule_id').val()) {
        return;
    }

    // keep any values the user has edited
    [['#start', 0], ['#end', 1], ['#start_recurring_dt', 0]].forEach(function ([selector, hours]) {
        if ($(selector).val() === scheduleDefaults[selector]) {
            setScheduleDefault(selector, hours);
        }
    });
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
    var schedule_id = $('#schedule_id').val();
    var formData = $('form.schedule-maintenance-form').serializeArray().filter(input => input.name !== 'schedule_id');
    $.ajax({
        type: schedule_id > 0 ? "PUT" : "POST",
        url: schedule_id > 0 ? route('alert-schedule.update', {alert_schedule: schedule_id}) : route('alert-schedule.store'),
        data: $.param(formData),
        dataType: "json",
        success: function(data){
            $("#message").html('<div id="schedulemsg" class="alert alert-info"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+data.message+'</div>');
            window.setTimeout(function() { $('#schedulemsg').fadeOut().slideUp(); } , 5000);
            $("#schedule-maintenance").modal('hide');
            $("#schedulemodal-alert").remove();
            $("#alert-schedule").bootgrid('reload');
        },
        error: function(jqXHR){
            var message = 'An error occurred.';
            if (jqXHR.responseJSON && jqXHR.responseJSON.errors) {
                message = Object.values(jqXHR.responseJSON.errors).flat().map(m => $('<div>').text(m).html()).join('<br />');
            } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                message = $('<div>').text(jqXHR.responseJSON.message).html();
            }
            $("#response").html('<div id="schedulemodal-alert" class="alert alert-danger">' + message + '</div>');
        }
    });
});

$("#maps").select2({
    width: '100%',
    placeholder: "Devices, Groups or Locations",
    ajax: {
        url: '<?php echo route('ajax.select.devices-groups-locations'); ?>',
        delay: 150
    }
});

$(function () {
    $("#start").datetimepicker({
        defaultDate: scheduleNow(),
        minDate: scheduleMinDate(),
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
        defaultDate: scheduleNow(1),
        minDate: scheduleMinDate(),
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
    setScheduleDefault('#start');
    setScheduleDefault('#end', 1);
    setScheduleDefault('#start_recurring_dt');
    $("#start").on("dp.change", function (e) {
        $("#end").data("DateTimePicker").minDate(e.date);
    });
    $("#end").on("dp.change", function (e) {
        $("#start").data("DateTimePicker").maxDate(e.date);
    });
    $("#start_recurring_dt").datetimepicker({
        defaultDate: scheduleNow(),
        minDate: scheduleMinDate(),
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
        minDate: scheduleMinDate(),
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
