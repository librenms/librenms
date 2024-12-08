@extends('layouts.librenmsv1')

@section('title', __('map.custom.title.manage'))

@section('content')
<div class="tw-px-3 sm:tw-px-6 tw-mx-auto">
    @include('map.custom-map-modal')
    @include('map.custom-map-delete-modal')
    @include('map.custom-map-clone-modal')

    <x-panel id="manage-custom-maps" body-class="!tw-pb-0" class="tw-mx-auto tw-max-w-screen-lg">
        <x-slot name="title">
            <div class="tw-flex tw-justify-between tw-items-center">
                <div>
                    {{ __('map.custom.title.manage') }}
                </div>
                <div>
                    <button class="btn btn-primary" onclick="$('#mapModal').modal({backdrop: 'static', keyboard: false}, 'show');">
                        {{ __('map.custom.create_map') }}
                    </button>
                </div>
            </div>
        </x-slot>

        @foreach($maps as $group_name => $group)
            <x-panel id="map-group-{{ $group_uuid = uniqid() }}" body-class="!tw-p-0">
                @if($group_name)
                    <x-slot name="title">{{ $group_name }}</x-slot>
                @endif
                @foreach($group as $map)
                    <div id="map-{{ $map->custom_map_id }}" class="even:tw-bg-gray-50 dark:even:tw-bg-zinc-900">
                        <div class="tw-flex tw-justify-between tw-p-3 tw-items-center hover:tw-bg-gray-100 dark:hover:tw-bg-gray-600">
                            <div>
                                <i class="fa fa-map-marked fa-fw fa-lg" aria-hidden="true"></i>
                                <a href="{{ route('maps.custom.show', $map->custom_map_id) }}">{{ $map->name }}</a>
                            </div>
                            <div class="tw-whitespace-nowrap">
                                <button class="btn btn-info"
                                        onclick="startMapClone(this)"
                                        data-map-name="{{ $map->name }}"
                                        data-map-id="{{ $map->custom_map_id }}"
                                ><i class="fa fa-copy" aria-hidden="true"></i>
                                    <span class="tw-hidden sm:tw-inline" aria-hidden="false">{{ __('Clone') }}</span>
                                </button>
                                <a class="btn btn-default" href="{{ route('maps.custom.edit', $map->custom_map_id) }}">
                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                    <span class="tw-hidden sm:tw-inline" aria-hidden="false">{{ __('Edit') }}</span>
                                </a>
                                <button class="btn btn-danger"
                                        onclick="startMapDelete(this)"
                                        data-map-name="{{ $map->name }}"
                                        data-map-group-id="#map-group-{{ $group_uuid }}"
                                        data-map-id="{{ $map->custom_map_id }}"
                                ><i class="fa fa-trash" aria-hidden="true"></i>
                                    <span class="tw-hidden sm:tw-inline" aria-hidden="false">{{ __('Delete') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </x-panel>
        @endforeach
    </x-panel>
</div>
@endsection

@section('scripts')
    @routes
<script type="text/javascript">
    var network_options = {{ Js::from($map_conf) }};
    var legend = {{ Js::from($legend) }};

    $('#mapDeleteModal').on('show.bs.modal', () => {
        let $mapDeleteModalLabel = $('#mapDeleteModalLabel');
        $mapDeleteModalLabel.text($mapDeleteModalLabel.data('text').replace(':name', pendingMapToDelete.name));
    });

    function editMapSuccess(data) {
        $('#mapModal').modal('hide');
        window.location.href = "{{ @route('maps.custom.edit', ['map' => '?']) }}".replace('?', data['id']);
    }

    function editMapCancel() {
        $('#mapModal').modal('hide');
    }

    var pendingMapToDelete;
    function startMapDelete(target) {
        let $target = $(target);
        pendingMapToDelete = {
            id: $target.data('map-id'),
            name: $target.data('map-name'),
            group_id: $target.data('map-group-id')
        };
        $('#mapDeleteModal').modal('show');
    }

    function deleteMap() {
        $.ajax({
            url: "{{ route('maps.custom.destroy', ['map' => '?']) }}".replace('?', pendingMapToDelete.id),
            type: 'DELETE'
        }).done(() => {
            $('#map-' + pendingMapToDelete.id).remove();
            if($(pendingMapToDelete.group_id + ' .panel-body').children().length === 0) {
                $(pendingMapToDelete.group_id).remove()
            }
            pendingMapToDelete = null;
            $('#mapDeleteModal').modal('hide');
        });
    }
</script>
@endsection
