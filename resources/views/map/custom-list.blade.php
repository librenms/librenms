@extends('layouts.librenmsv1')

@section('title', __('Custom Maps'))

@section('content')
<div class="tw:px-3 tw:sm:px-6 tw:mx-auto">
    <x-panel id="manage-custom-maps" body-class="tw:pb-0!" class="tw:mx-auto tw:max-w-(--breakpoint-lg)">
        <x-slot name="title">
            <div class="tw:flex tw:justify-between tw:items-center">
                <div>
                    {{ __('Custom Maps') }}
                </div>
            </div>
        </x-slot>

        <x-accordion accordionId="CustomMapGroups">
            @foreach($maps as $group_name => $group)
                <x-accordion.item title="{{$group_name ?: 'Ungrouped'}}" id="{{uniqid()}}" open="{{($open_group == $group_name) || (count($maps) == 1)}}">
                @foreach($group as $map)
                    <div id="map-{{ $map->custom_map_id }}" class="tw:even:bg-gray-50 tw:dark:even:bg-zinc-900">
                        <div class="tw:flex tw:justify-between tw:p-3 tw:items-center tw:hover:bg-gray-100 tw:dark:hover:bg-gray-600">
                            <div>
                                <i class="fa fa-map-marked fa-fw fa-lg" aria-hidden="true"></i>
                                <a href="{{ route('maps.custom.show', $map->custom_map_id) }}">{!! $map->name !!}</a>
                            </div>
                        </div>
                    </div>
                @endforeach
                </x-accordion.item>
            @endforeach
        </x-accordion>
    </x-panel>
</div>
@endsection
