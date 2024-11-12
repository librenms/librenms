<div class="modal fade" id="imageDeleteModal" tabindex="-1" role="dialog" aria-labelledby="imageDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageDeleteModalLabel" data-text="{{ __('map.custom.nodeimage.delete') }} :name ?"></h5>
            </div>
            <div class="modal-footer">
                <center>
                    <button type=button value="delete" id="image-deleteConfirmButton" class="btn btn-danger" onclick="deleteImage()">{{ __('Delete') }}</button>
                    <button type=button value="cancel" id="image-deleteCancelButton" class="btn btn-primary" onclick="$('#imageDeleteModal').modal('hide');">{{ __('Cancel') }}</button>
                </center>
            </div>
            <div>
              <center>
                <div class="tw-text-red-600" id="imagedeleteerror"></div>
              </center>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#imageDeleteModal').on('show.bs.modal', () => {
        let $imageDeleteModalLabel = $('#imageDeleteModalLabel');
        $imageDeleteModalLabel.text($imageDeleteModalLabel.data('text').replace(':name', pendingImageToDelete.name));
    });

    var pendingImageToDelete;
    function startImageDelete(target) {
        let $target = $(target);
        pendingImageToDelete = {
            id: $target.data('image-id'),
            name: $target.data('image-name'),
        };
        $('#imageDeleteModal').modal('show');
        $('#imageDeleteModal').modal('show');
    }

    function deleteImage() {
        $.ajax({
            url: "{{ route('maps.nodeimage.destroy', ['image' => '?']) }}".replace('?', pendingImageToDelete.id),
            type: 'DELETE'
        }).done(() => {
            $('#image-' + pendingImageToDelete.id).remove();
            pendingMapToDelete = null;
            $('#imageDeleteModal').modal('hide');
            $('#imagedeleteerror').text('');
        }).fail((jqXHR, textStatus, errorThrown) => {
            $('#imagedeleteerror').text('Error deleting: ' + jqXHR.responseText);
        });
    }
</script>
