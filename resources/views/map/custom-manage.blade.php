@extends('layouts.librenmsv1')

@section('title', __('map.custom.title.manage'))

@section('content')
<div class="container-fluid">
    @include('map.custom-map-modal')
    @include('map.custom-map-delete-modal')

    <x-panel body-class="!tw-p-0" id="manage-custom-maps">
        <x-slot name="title">
            <div class="tw-flex tw-justify-between tw-items-center">
                <div>
                    <i class="fa fa-map-marked fa-fw fa-lg" aria-hidden="true"></i> {{ __('map.custom.title.manage') }}
                </div>
                <div>
                    <button class="btn btn-primary" onclick="$('#mapModal').modal({backdrop: 'static', keyboard: false}, 'show');">{{ __('map.custom.create_map') }}</button>
                </div>
            </div>
        </x-slot>

        <table class="table table-striped table-condensed" style="margin-bottom: 0; margin-top: -1px">
            @foreach($maps as $map)
                <tr id="map{{ $map->custom_map_id }}">
                    <td style="vertical-align: middle">
                        <a href="{{ route('maps.custom.show', $map->custom_map_id) }}">{{ $map->name }}</a>
                    </td>
                    <td>
                        <a class="btn btn-primary" href="{{ route('maps.custom.edit', $map->custom_map_id) }}"><i class="fa fa-pencil"></i> {{ __('Edit') }}</a>
                        <button class="btn btn-danger"
                                onclick="startMapDelete(this)"
                                data-map-id="{{ $map->custom_map_id }}"
                        ><i class="fa fa-trash"></i> {{ __('Delete') }}</button>
                    </td>
                </tr>
            @endforeach
        </table>
    </x-panel>
</div>
@endsection

@section('scripts')
    @routes
<script type="text/javascript">
    $("#mapBackgroundClearRow").hide();

    function editMapSuccess(data) {
        $('#mapModal').modal('hide');
        window.location.href = "{{ @route('maps.custom.edit', ['map' => '?']) }}".replace('?', data['id']);
    }

    function editMapCancel() {
        $('#mapModal').modal('hide');
    }

    var pendingDeleteId;
    function startMapDelete(target) {
        pendingDeleteId = $(target).data('map-id');
        $('#mapDeleteModal').modal({backdrop: 'static', keyboard: false}, 'show');
    }

    function deleteMap() {
        $.ajax({
            url: "{{ route('maps.custom.destroy', ['map' => '?']) }}".replace('?', pendingDeleteId),
            type: 'DELETE'
        }).done(() => {
                $('#map' + pendingDeleteId).remove();
                pendingDeleteId = null;
                $('#mapDeleteModal').modal('hide');
            });
    }
</script>
@endsection
