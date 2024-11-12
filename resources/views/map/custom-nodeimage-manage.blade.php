@extends('layouts.librenmsv1')

@section('title', __('map.custom.nodeimage.title.manage'))

@section('content')
<div class="tw-px-3 sm:tw-px-6 tw-mx-auto">
    @include('map.custom-nodeimage-modal')
    @include('map.custom-nodeimage-delete-modal')
    <x-panel id="manage-custom-nodeimages" body-class="!tw-pb-0" class="tw-mx-auto tw-max-w-screen-lg">
        <x-slot name="title">
            <div class="tw-flex tw-justify-between tw-items-center">
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

        @foreach($images as $image)
            <div id="image-{{ $image->custom_map_node_image_id }}" class="even:tw-bg-gray-50 dark:even:tw-bg-zinc-900">
                <div class="tw-flex tw-justify-between tw-p-3 tw-items-center hover:tw-bg-gray-100 dark:hover:tw-bg-gray-600">
                    <div id="imagename-{{ $image->custom_map_node_image_id }}">{{ $image->name }}</div>
                    <div>
                        <img src="{{ route('maps.nodeimage.show', ['image' => $image->custom_map_node_image_id ]) }}" width="25" height="25">
                    </div>
                    <div class="tw-whitespace-nowrap">
                        <button class="btn btn-default" onclick="imageModalEdit({{ $image->custom_map_node_image_id }});">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                            <span class="tw-hidden sm:tw-inline" aria-hidden="false">{{ __('Edit') }}</span>
                        </button>
                        <button class="btn btn-danger"
                                onclick="startImageDelete(this)"
                                data-image-name="{{ $image->name }}"
                                data-image-id="{{ $image->custom_map_node_image_id}}"
                        ><i class="fa fa-trash" aria-hidden="true"></i>
                            <span class="tw-hidden sm:tw-inline" aria-hidden="false">{{ __('Delete') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </x-panel>
</div>
@endsection
