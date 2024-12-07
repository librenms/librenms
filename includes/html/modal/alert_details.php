<form>
    <?php echo csrf_field() ?>
    <div class="modal fade" id="alert_details_modal" tabindex="-1" role="dialog" aria-labelledby="alert_details" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="alert_details">Alert details</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class='col-sm-12'>
                            <div class="form-group">
                                <textarea class="form-control" id="details" name="details" rows="20"></textarea>
                                  <input type="hidden" id="alert_log_id" name="alert_log_id" value="">
                            </div>
                        </div>
                    </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    $('#alert_details_modal').on('show.bs.modal', function (event) {
        var alert_log_id = $("#alert_log_id").val();
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: { type: "alert-details", 'alert_log_id': alert_log_id, sub_type: 'get_details'},
            dataType: "json",
            success: function (data) {
                $("#details").val(JSON.stringify(data.details,null, 2));
            }
        });
    });
</script>
