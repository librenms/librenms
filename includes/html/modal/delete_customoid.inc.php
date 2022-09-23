<?php

if (! (Auth::user()->hasGlobalAdmin())) {
    exit('ERROR: You need to be admin');
}

?>

<div class="modal fade" id="delete-oid-form" tabindex="-1" role="dialog" aria-labelledby="Delete" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="Delete">Confirm Delete</h5>
            </div>
            <div class="modal-body">
                <p>If you would like to remove this OID then please click Delete.</p>
            </div>
            <div class="modal-footer">
                <form role="form" class="remove_oid_form">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger danger" id="delete-oid-button" data-target="delete-oid-button">Delete</button>
                    <input type="hidden" name="dcustomoid_id" id="dcustomoid_id" value="">
                    <input type="hidden" name="confirm" id="confirm" value="yes">
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$('#delete-oid-form').on('show.bs.modal', function(event) {
    customoid_id = $(event.relatedTarget).data('customoid_id');
    $("#dcustomoid_id").val(customoid_id);
});

$('#delete-oid-button').on('click', function(event) {
    event.preventDefault();
    var customoid_id = $("#dcustomoid_id").val();
    $.ajax({
        type: 'POST',
        url: 'ajax_form.php',
        data: { type: "delete-customoid", customoid_id: customoid_id },
        dataType: "html",
        success: function(msg) {
            if(msg.indexOf("ERROR:") <= -1) {
                $("#row_"+customoid_id).remove();
            }
            $("#message").html('<div class="alert alert-info">'+msg+'</div>');
            $("#delete-oid-form").modal('hide');
        },
        error: function() {
            $("#message").html('<div class="alert alert-info">This OID could not be deleted.</div>');
            $("#delete-oid-form").modal('hide');
        }
    });
});
</script>
