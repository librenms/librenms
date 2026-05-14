@extends('layouts.librenmsv1')

@section('title', __('Devices'))

@section('content')
    <div class="container-fluid">
        <x-panel>
            <x-slot name="heading">
                <div class="tw:flex tw:justify-between">
                    <div class="tw:min-h-8">
                        <x-option-bar :options="$nav" name="{{ __('Lists') }}" :selected="'list_' . $subformat" linkClass="sync-filter-url" border="none" class="tw:inline-block tw:p-1"></x-option-bar>
                        <x-option-bar :options="$graphNav" name="{{ __('Graphs') }}" :selected="'graph_' . $subformat" linkClass="sync-filter-url" border="none" class="tw:inline-block tw:p-1"></x-option-bar>
                    </div>
                    <div class="btn-group pull-right" role="group">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-ellipsis-v fa-lg fa-fw icon-theme"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="{{ $hideFilterLink }}"><i class="fa fa-regular @if($hideFilter) fa-square @else fa-square-check @endif fa-lg fa-fw icon-theme" aria-hidden="true"></i> {{ __('device.show_filter') }}</a></li>
                                <li><a href="{{ $bareLink }}"><i class="fa fa-regular @if($bare) fa-square @else fa-square-check @endif fa-lg fa-fw icon-theme" aria-hidden="true"></i> {{ __('device.show_header') }}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </x-slot>
            @if($view === 'graph')
                @include('device.graphs')
            @else
                @include('device.list')
            @endif
        </x-panel>

    </div>
@endsection
