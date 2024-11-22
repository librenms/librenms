<div class="modal fade" id="mapCloneModal" tabindex="-1" role="dialog" aria-labelledby="mapCloneModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapCloneModalLabel" data-text="{{ __('map.custom.edit.map.clone') }}"></h5>
            </div>
            <div class="modal-footer">
                <center>
                    <button type=button value="clone" id="map-cloneConfirmButton" class="btn btn-info" onclick="cloneMap()">{{ __('Clone') }}</button>
                    <button type=button value="cancel" id="map-cloneCancelButton" class="btn btn-primary" onclick="$('#mapCloneModal').modal('hide');">{{ __('Cancel') }}</button>
                </center>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script type="text/javascript">
    $('#mapCloneModal').on('show.bs.modal', () => {
        let $mapCloneModalLabel = $('#mapCloneModalLabel');
        $mapCloneModalLabel.text($mapCloneModalLabel.data('text').replace(':name', pendingMapToClone.name));
    });

    var pendingMapToClone;
    function startMapClone(target) {
        let $target = $(target);
        pendingMapToClone = {
            id: $target.data('map-id'),
            name: $target.data('map-name')
        };
        $('#mapCloneModal').modal('show');
    }

    function cloneMap() {
        $.ajax({
            url: "{{ route('maps.custom.clone', ['map' => '?']) }}".replace('?', pendingMapToClone.id),
            type: 'POST',
            data: {},
            dataType: 'json',
        }).done(function (data, status, resp) {
            window.location.href = "{{ @route('maps.custom.edit', ['map' => '?']) }}".replace('?', data['id']);
        });
    }
</script>
@endpush
