<div class="modal fade" id="mapListModal" tabindex="-1" role="dialog" aria-labelledby="mapListModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapListModalLabel">{{ __('map.custom.edit.map.unsavedchanges') }}</h5>
            </div>
            <div class="modal-footer">
                <center>
                    <button type=button value="list" id="map-listConfirmButton" class="btn btn-danger" onclick="viewList()">{{ __('Confirm') }}</button>
                    <button type=button value="cancel" id="map-listCancelButton" class="btn btn-primary" onclick="$('#mapListModal').modal('hide');">{{ __('Cancel') }}</button>
                </center>
            </div>
        </div>
    </div>
</div>
