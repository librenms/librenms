@extends('layouts.librenmsv1')

@section('title', $device->displayName() . ' ' . $title)

@section('content')
    <div class="container-fluid">
        @include('device.header')

        <ul class="nav nav-tabs">
            @foreach($tabs as $tab)
                @if($tab->visible($device))
                    <li role="presentation" @if( $current_tab == $tab->slug() ) class="active" @endif>
                        <a href="{{ route('device', [$device_id, $tab->slug()]) }}"><i class="fa {{ $tab->icon() }} fa-lg icon-theme" aria-hidden="true"></i>&nbsp;{{ $tab->name() }}&nbsp;</a>
                    </li>
                @endif
            @endforeach
        </ul>

        <div class="tab-content">
            <div class="tab-pane active">
                @yield('tab')
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .tab-content {
            margin-top: 8px;
        }
    </style>
@endsection
