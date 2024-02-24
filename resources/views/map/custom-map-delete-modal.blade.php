<div class="modal fade" id="mapDeleteModal" tabindex="-1" role="dialog" aria-labelledby="mapDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapDeleteModalLabel">{{ __('map.custom.edit.map.delete') }}</h5>
            </div>
            <div class="modal-footer">
                <center>
                    <button type=button value="delete" id="map-deleteConfirmButton" class="btn btn-danger" onclick="deleteMap()">{{ __('Delete') }}</button>
                    <button type=button value="cancel" id="map-deleteCancelButton" class="btn btn-primary" onclick="$('#mapDeleteModal').modal('hide');">{{ __('Cancel') }}</button>
                </center>
            </div>
        </div>
    </div>
</div>
