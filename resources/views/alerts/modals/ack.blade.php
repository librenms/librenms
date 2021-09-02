<form class="form-horizontal">
    @csrf
    <div class="modal fade" id="alert_ack_modal" tabindex="-1" role="dialog" aria-labelledby="alert_notes" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="alert_notes">@lang('Acknowledge Alert')</h5>
                </div>
                <div class="modal-body">
                    <div class='form-group'>
                        <label for='ack_msg' class='col-sm-4 col-md-3 control-label' title="@lang('Add a message to the acknowledgement')">@lang('(Un)Acknowledgement note:')</label>
                        <div class="col-sm-8 col-md-9">
                            <input type='text' id='ack_msg' name='ack_msg' class='form-control' autofocus>
                        </div>
                    </div>
                    <div class="form-group" id="ack_section">
                        <label for="ack_until_clear" class="col-sm-4 col-md-3 control-label" title="@lang('Acknowledge until alert clears')">@lang('Acknowledge until clear:')</label>
                        <div class="col-sm-8 col-md-9">
                            <input type='checkbox' name='ack_until_clear' id='ack_until_clear'>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-md-offset-3 col-sm-3 col-md-2">
                            <input type="hidden" id="ack_alert_id" name="ack_alert_id" value="">
                            <input type="hidden" id="ack_alert_state" name="ack_alert_state" value="">
                            <button class="btn btn-success" id="ack-alert" name="ack-alert">@lang('Ack alert')</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script type="text/javascript">
    $('#alert_ack_modal').on('show.bs.modal', function() {
        if ($("#ack_alert_state").val() == 2) {
            var button_label = 'Un-acknowledge alert';
            $('#ack_section').hide();
        } else {
            var button_label = 'Acknowledge alert';
            $('#ack_section').show();
        }
        document.getElementById('ack-alert').innerText = button_label;
        $("#ack_until_clear").bootstrapSwitch('state', {{ Config::get('alert.ack_until_clear') ? 'true' : 'false' }});
    });

    $("#ack-alert").on("click", function(event) {
        event.preventDefault();
        var ack_alert_id = $("#ack_alert_id").val();
        var ack_alert_note = $('#ack_msg').val();
        var ack_alert_state = $("#ack_alert_state").val();
        var ack_until_clear = $("#ack_until_clear").bootstrapSwitch('state');
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            dataType: "json",
            data: {
                type: "ack-alert",
                alert_id: ack_alert_id,
                state: ack_alert_state,
                ack_msg: ack_alert_note,
                ack_until_clear: ack_until_clear
            },
            success: function(data) {
                if (data.status === "ok") {
                    toastr.success(data.message);
                    var $table = $('table.alerts');
                    var sortDictionary = $table.bootgrid("getSortDictionary");
                    $table.bootgrid('reload');
                    $table.bootgrid("sort", sortDictionary);
                    $("#alert_ack_modal").modal('hide');
                } else {
                    toastr.error(data.message);
                }
            },
            error: function() {
                toastr.error(data.message);
            }
        });
    });
</script>
@endpush
