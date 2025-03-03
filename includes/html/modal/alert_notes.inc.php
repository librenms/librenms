<form>
    <?php echo csrf_field() ?>
    <div class="modal fade" id="alert_notes_modal" tabindex="-1" role="dialog" aria-labelledby="alert_notes" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="alert_notes">Alert notes</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class='col-sm-12'>
                            <div class="form-group">
                                <textarea class="form-control" id="note" name="note" rows="15"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class='col-sm-12'>
                            <div class="form-group">
                                <input type="hidden" id="alert_id" name="alert_id" value="">
                                <button class="btn btn-success" id="save-alert-notes" name="save-alert-notes">Save notes</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    $('#alert_notes_modal').on('show.bs.modal', function (event) {
        var alert_id = $("#alert_id").val();
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: { type: "alert-notes", alert_id: alert_id, sub_type: 'get_note'},
            dataType: "json",
            success: function (data) {
                $("#note").val(data.note);
            }
        });
    });
    $("#save-alert-notes").on("click", function(event) {
        event.preventDefault();
        var alert_id = $("#alert_id").val();
        var note = $("#note").val();
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: { type: "alert-notes", alert_id: alert_id, sub_type: 'set_note', note: note},
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    toastr.success(data.message);
                    $("#alert_notes_modal").modal('hide');
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
