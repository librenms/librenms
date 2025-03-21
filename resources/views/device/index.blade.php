@extends('layouts.librenmsv1')

@section('title', $device->displayName() . ' ' . $title)

@section('content')
    <div class="container-fluid">
        @include('device.header')

        <ul class="nav nav-tabs">
            @foreach($tabs as $tab)
                @if($tab->visible($device))
                    <li role="presentation" @if( $current_tab == $tab->slug() ) class="active" @endif>
                        <a href="{{ route('device', [$device_id, $tab->slug()]) }}" class="tw:whitespace-nowrap">
                            <i class="fa {{ $tab->icon() }} fa-lg icon-theme" aria-hidden="true"></i>
                            {{ $tab->name() }}
                        </a>
                    </li>
                @endif
            @endforeach

            <div class="btn-group pull-right" role="group">
                <a href="{{ $primary_device_link['url'] }}"
                   class="btn btn-default"
                   type="button"
                   @if(isset($primary_device_link['onclick']))onclick="{{ $primary_device_link['onclick'] }}" @endif
                   @if($primary_device_link['external'])target="_blank" rel="noopener" @endif
                   title="{{ $primary_device_link['title'] }}"
                >&nbsp;<i class="fa {{ $primary_device_link['icon'] }} fa-lg icon-theme"></i>
                </a>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        &nbsp;<i class="fa fa-ellipsis-v fa-lg icon-theme"></i>&nbsp;
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        @foreach($device_links as $link)
                            <li><a href="{{ $link['url'] }}"
                                   @if(isset($link['onclick']))onclick="{{ $link['onclick'] }}" @endif
                                   @if($link['external'])target="_blank" rel="noopener" @endif
                                ><i class="fa {{ $link['icon'] }} fa-lg fa-fw icon-theme" aria-hidden="true"></i> {{ $link['title'] }}</a></li>
                        @endforeach
                        @if($page_links)
                            <li role="presentation" class="divider"></li>
                                @foreach($page_links as $link)
                                    <li><a href="{{ $link['url'] }}"
                                           @if(isset($link['onclick']))onclick="{{ $link['onclick'] }}" @endif
                                           @if($link['external'])target="_blank" rel="noopener" @endif
                                        ><i class="fa {{ $link['icon'] }} fa-lg fa-fw icon-theme" aria-hidden="true"></i> {{ $link['title'] }}</a></li>
                                @endforeach
                        @endif
                    </ul>
                </div>
            </div>
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
