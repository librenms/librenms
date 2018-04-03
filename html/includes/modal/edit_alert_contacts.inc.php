<?php
if (is_admin() === false) {
    die('ERROR: Must be admin.');
}
?>
    <div class="modal fade" id="edit-alert-contacts" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title">Alert Contacts :: <a href="https://docs.librenms.org/Alerting/">Docs <i class="fa fa-book fa-1x"></i></a> </h5>
                </div>
                <div class="modal-body">
                    <form method="post" role="form" id="contacts" class="form-horizontal contacts-form">
                        <input class="hidden" id="type" name="type" value="alert-contacts">
                        <div class='form-group'>
                            <label for='name' class='col-sm-3 control-label'>Contact Name: </label>
                            <div class='col-sm-9'>
                                <input type='text' id='contact-name' name='contact-name' class='form-control validation' maxlength='200' required>
                            </div>
                        </div>
                        <div class="form-group form-inline">
                            <label for="transport-type" class="col-sm-3 control-label">Transport Type: </label>
                            <div class="col-sm-2">
                                <select name="transport-type" id="transport-type" class="form-control">
                                    <option value="email" selected>Email</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <input type="text" id="member" name="member" class="form-control" size="30" placeholder="Contact Detail (ie. Email)" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-3">
                                <button type="button" class="btn btn-success" id="save-contact-btn" name="save-contact">
                                    Save Contact
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
    
    $("#save-contact-btn").on('click', function (e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: $('form.contacts-form').serializeArray(),
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    toastr.success(data.message);
                    $('#edit-alert-contacts').modal('hide');
                    window.location.reload();
                } else {
                    toastr.error(data.message);
                }
            },
            error: function () {
                toastr.error('Failed to process contact form');
            }
        });
    });

    </script>

