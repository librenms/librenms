@extends('layouts.librenmsv1')

@section('title', __('map.custom.nodeimage.title.manage'))

@section('content')
<div class="tw:px-3 tw:sm:px-6 tw:mx-auto">
    @include('map.custom-nodeimage-modal')
    @include('map.custom-nodeimage-delete-modal')
    <x-panel id="manage-custom-nodeimages" body-class="tw:pb-0!" class="tw:mx-auto tw:max-w-(--breakpoint-lg)">
        <x-slot name="title">
            <div class="tw:flex tw:justify-between tw:items-center">
                <div>
                    {{ __('map.custom.nodeimage.title.manage') }}
                </div>
                <div>
                    <button class="btn btn-primary" onclick="imageModalEdit(null);">
                        {{ __('map.custom.nodeimage.new') }}
                    </button>
                </div>
            </div>
        </x-slot>

    </x-panel>
</div>
@endsection

@section('scripts')
<script>
    function appendImageRow(node_image_id, name) {
        $("#manage-custom-nodeimages").append('' +
'        <div id="image-' + node_image_id + '" class="tw:even:bg-gray-50 tw:dark:even:bg-zinc-900">' +
'            <div class="tw:flex tw:justify-between tw:p-3 tw:items-center tw:hover:bg-gray-100 tw:dark:hover:bg-gray-600">' +
'                <div id="imagename-' + node_image_id + '">' + name + '</div>' +
'                <div>' +
'                    <img id="imageview-' + node_image_id + '" src="' + '{{ route('maps.nodeimage.show', ['image' => '?' ]) }}'.replace("?", node_image_id) + '" width="25" height="25">' +
'                </div>' +
'                <div class="tw:whitespace-nowrap">' +
'                    <button class="btn btn-default" onclick="imageModalEdit(' + node_image_id + ');">' +
'                        <i class="fa fa-pencil" aria-hidden="true"></i>' +
'                        <span class="tw:hidden tw:sm:inline" aria-hidden="false">{{ __('Edit') }}</span>' +
'                    </button>' +
'                    <button class="btn btn-danger"' +
'                            onclick="startImageDelete(this)"' +
'                            data-image-name="' + name + '"' +
'                            data-image-id="' + node_image_id + '"' +
'                    ><i class="fa fa-trash" aria-hidden="true"></i>' +
'                        <span class="tw:hidden tw:sm:inline" aria-hidden="false">{{ __('Delete') }}</span>' +
'                    </button>' +
'                </div>' +
'            </div>' +
'        </div>' +
        '');
    }

    $(document).ready(function () {
        @foreach($images as $image)
        appendImageRow({{ $image->custom_map_node_image_id }}, '{{ $image->name }}');
        @endforeach
    });
</script>
@endsection
