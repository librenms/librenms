<?php

if (! (Auth::user()->hasGlobalAdmin())) {
    exit('ERROR: You need to be admin');
}

?>

<div class="modal fade" id="create-oid-form" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="Create">Custom OID :: <a target="_blank" href="https://docs.librenms.org/">Docs <i class="fa fa-book fa-1x"></i></a> </h5>
            </div>
            <div class="modal-body">
                <form method="post" role="form" id="coids" class="form-horizontal coid_form">
                    <input type="hidden" name="device_id" id="device_id" value="<?php echo isset($device['device_id']) ? $device['device_id'] : -1; ?>">
                    <input type="hidden" name="device_name" id="device_name" value="<?php echo format_hostname($device); ?>">
                    <input type="hidden" name="ccustomoid_id" id="ccustomoid_id" value="">
                    <input type="hidden" name="type" id="type" value="customoid">
                    <input type="hidden" name="action" id="action" value="">
                    <div class='form-group' title="A description of the OID">
                        <label for='name' class='col-sm-4 col-md-3 control-label'>Name: </label>
                        <div class='col-sm-8 col-md-9'>
                            <input type='text' id='name' name='name' class='form-control validation' maxlength='200' required>
                        </div>
                    </div>
                    <div class="form-group" title="SNMP OID">
                        <label for='oid' class='col-sm-4 col-md-3 control-label'>OID: </label>
                        <div class='col-sm-8 col-md-9'>
                            <input type='text' id='oid' name='oid' class='form-control validation' maxlength='255' required>
                        </div>
                    </div>
                    <div class="form-group" title="SNMP data type">
                        <label for='datatype' class='col-sm-4 col-md-3 control-label'>Data Type: </label>
                        <div class='col-sm-8 col-md-9'>
                            <select class="form-control" id="datatype" name="datatype">
                                <option value="COUNTER">COUNTER</option>
                                <option value="GAUGE">GAUGE</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" title="Unit of value being polled">
                        <label for='unit' class='col-sm-4 col-md-3 control-label'>Unit: </label>
                        <div class='col-sm-8 col-md-9'>
                            <input type='text' id='unit' name='unit' class='form-control validation' maxlength='10'>
                        </div>
                    </div>
                    <div class='form-group form-inline'>
                        <label class='col-sm-4 col-md-3 control-label'>Calculations: </label>
                        <div class="col-sm-8">
                            <label for='divisor' class='col-sm-4 col-md-3 control-label' title="Divide raw SNMP value by">Divisor: </label>
                            <div class="col-sm-4 col-md-3" title="Divide raw SNMP value by">
                                <input type='text' id='divisor' name='divisor' class='form-control' size="4">
                            </div>
                            <label for='multiplier' class='col-sm-4 col-md-3 control-label' title="Multiply raw SNMP value by">Multiplier: </label>
                            <div class="col-sm-4 col-md-3" title="Multiply raw SNMP value by">
                                <input type='text' id='multiplier' name='multiplier' class='form-control' size="4">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" title="User function to apply to value">
                        <label for='user_func' class='col-sm-4 col-md-3 control-label'>User Function: </label>
                        <div class='col-sm-8 col-md-9'>
                            <select class="form-control" id="user_func" name="user_func">
                                <option value=""></option>
                                <option value="celsius_to_fahrenheit">C to F</option>
                                <option value="fahrenheit_to_celsius">F to C</option>
                                <option value="uw_to_dbm">uW to dBm</option>
                            </select>
                        </div>
                    </div>
                    <div class='form-group form-inline'>
                        <label class='col-sm-4 col-md-3 control-label'>Alert Thresholds: </label>
                        <div class="col-sm-8">
                            <label for='limit' class='col-sm-4 col-md-3 control-label' title="Level to alert above">High: </label>
                            <div class="col-sm-4 col-md-3" title="Level to alert above">
                                <input type='text' id='limit' name='limit' class='form-control' size="4">
                            </div>
                            <label for='limit_low' class='col-sm-4 col-md-3 control-label' title="Level to alert below">Low: </label>
                            <div class="col-sm-4 col-md-3" title="Level to alert below">
                                <input type='text' id='limit_low' name='limit_low' class='form-control' size="4">
                            </div>
                        </div>
                    </div>
                    <div class='form-group form-inline'>
                        <label class='col-sm-4 col-md-3 control-label'>Warning Thresholds: </label>
                        <div class="col-sm-8">
                            <label for='limit_warn' class='col-sm-4 col-md-3 control-label' title="Level to warn above">High: </label>
                            <div class="col-sm-4 col-md-3" title="Level to warn above">
                                <input type='text' id='limit_warn' name='limit_warn' class='form-control' size="4">
                            </div>
                            <label for='limit_low_warn' class='col-sm-4 col-md-3 control-label' title="Level to warn below">Low: </label>
                            <div class="col-sm-4 col-md-3" title="Level to warn below">
                               <input type='text' id='limit_low_warn' name='limit_low_warn' class='form-control' size="4">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" title="Alerts for this OID enabled">
                        <label for='alerts' class='col-sm-4 col-md-3 control-label'>Alerts Enabled: </label>
                        <div class='col-sm-4 col-md-3'>
                            <input type='checkbox' name='alerts' id='alerts'>
                        </div>
                        <label for='passed' class='col-sm-4 col-md-3 control-label'>Passed Check: </label>
                        <div class='col-sm-4 col-md-3'>
                            <input type='checkbox' name='cpassed' id='cpassed' disabled>
                            <input type='hidden' name='passed' id='passed' value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12 text-center">
                            <button type="button" class="btn btn-success" id="save-oid-button" name="save-oid-button">
                                Save OID
                            </button>
                            <button type="button" class="btn btn-primary" id="test-oid-button" name="test-oid-button">
                                Test OID
                            </button>
                        </div>
                    </div>
                    <div class='form-group form-inline'>
                        <div class="col-sm-12">
                            <p><small><em>OID will not be polled until a test is successfully complete.</em></small></p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$('#create-oid-form').on('show.bs.modal', function(e) {
    var customoid_id = $(e.relatedTarget).data('customoid_id');
    $('#ccustomoid_id').val(customoid_id);
    if (customoid_id >= 0) {
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: { type: "parse-customoid", customoid_id: customoid_id },
                dataType: "json",
                success: function (data) {
                    $('#name').val(data.name);
                    $('#oid').val(data.oid);
                    $('#datatype').val(data.datatype);
                    $('#datatype').prop('disabled', true);
                    $('#unit').val(data.unit);
                    $('#divisor').val(data.divisor);
                    $('#multiplier').val(data.multiplier);
                    $('#user_func').val(data.user_func);
                    $('#limit').val(data.limit);
                    $('#limit_warn').val(data.limit_warn);
                    $('#limit_low').val(data.limit_low);
                    $('#limit_low_warn').val(data.limit_low_warn);
                    $('#alerts').prop('checked', data.alerts);
                    $('#passed').val(data.passed);
                    $('#cpassed').prop('checked', data.passed);
                }
        });
    } else {
        $('#name').val('');
        $('#oid').val('');
        $('#datatype').val('GAUGE');
        $('#datatype').prop('disabled', false);
        $('#unit').val('');
        $('#divisor').val('');
        $('#multiplier').val('');
        $('#user_func').val('');
        $('#limit').val('');
        $('#limit_warn').val('');
        $('#limit_low').val('');
        $('#limit_low_warn').val('');
        $('#alerts').prop('checked', false);
        $('#passed').val('');
        $('#cpassed').prop('checked', false);
    }
});
$('#save-oid-button').on('click', function (e) {
    e.preventDefault();
    $('#action').val('save');
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: $('form.coid_form').serializeArray(),
        dataType: "json",
        success: function (data) {
            if (data.status == 'ok') {
                toastr.success(data.message);
                $('#create-oid-form').modal('hide');
                window.location.reload();
            } else {
                toastr.error(data.message);
            }
        },
        error: function (exception) {
            toastr.error('Failed to process OID');
        }
    });
});
$('#test-oid-button').on('click', function (e) {
    e.preventDefault();
    $('#action').val('test');
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: $('form.coid_form').serializeArray(),
        dataType: "json",
        success: function (data) {
            if (data.status == 'ok') {
                toastr.success(data.message);
                $("#passed").val('on');
                $("#cpassed").prop("checked", true);
            } else {
                toastr.error(data.message);
            }
        },
        error: function (exception) {
            toastr.error('Failed to process OID');
        }
    });
});
$('#oid').on("change", function () {
    $("#passed").val('');
    $("#cpassed").prop("checked", false);
});
</script>
