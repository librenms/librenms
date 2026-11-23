<form id="alert-notes-form" method="post">
    @csrf
    <div class="modal fade" id="alert_notes_modal" tabindex="-1" role="dialog" aria-labelledby="alert_notes_title" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="alert_notes_title">{{ __('Alert notes') }}</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class='col-sm-12'>
                            <div class="form-group">
                                <textarea class="form-control" id="note" name="note" rows="15" aria-labelledby="alert_notes_title" disabled></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class='col-sm-12'>
                            <div class="form-group">
                                <input type="hidden" id="alert_id" name="alert_id" value="">
                                <button type="submit" class="btn btn-success" id="save-alert-notes" disabled>{{ __('Save notes') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(function () {
        const $form = $('#alert-notes-form');
        const $modal = $form.find('#alert_notes_modal');
        const $note = $form.find('#note');
        const $save = $form.find('#save-alert-notes');
        const $fields = $note.add($save);
        let currentFault = null;

        $modal.on('show.bs.modal', function () {
            const faultId = encodeURIComponent($form.find('#alert_id').val());
            const fault = {
                show: @js(route('fault.notes.show', ['fault' => '__FAULT_ID__'])).replace('__FAULT_ID__', faultId),
                update: @js(route('fault.notes.update', ['fault' => '__FAULT_ID__'])).replace('__FAULT_ID__', faultId)
            };
            currentFault = fault;

            $note.val('');
            $fields.prop('disabled', true);

            $.getJSON(fault.show).done(function (data) {
                if (currentFault === fault) {
                    $note.val(data.note);
                    $fields.prop('disabled', false);
                }
            }).fail(function (xhr) {
                if (currentFault === fault) {
                    toastr.error(document.createTextNode(xhr.responseJSON?.message || @js(__('Could not load note'))));
                }
            });
        }).on('hide.bs.modal', function () {
            currentFault = null;
        });

        $form.on('submit', function (event) {
            event.preventDefault();
            const fault = currentFault;
            if (!fault || $save.prop('disabled')) {
                return;
            }

            $fields.prop('disabled', true);
            $.ajax({
                type: 'PATCH',
                url: fault.update,
                data: {note: $note.val()},
                dataType: 'json'
            }).done(function (data) {
                if (currentFault === fault) {
                    toastr.success(document.createTextNode(data.message));
                    $modal.modal('hide');
                }
            }).fail(function (xhr) {
                if (currentFault === fault) {
                    $fields.prop('disabled', false);
                    toastr.error(document.createTextNode(xhr.responseJSON?.message || @js(__('Could not update note'))));
                }
            });
        });
    });
</script>
