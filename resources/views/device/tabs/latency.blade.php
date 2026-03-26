@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    @if($data['smokeping']->hasGraphs())
        <x-panel class="with-nav-tabs">
            <x-slot name="heading">
                @if(\App\Facades\LibrenmsConfig::get('smokeping.url'))
                    <a href="{{ \App\Facades\LibrenmsConfig::get('smokeping.url') }}?target={{ $device->type }}.{{ str_replace('.','_',$device->hostname) }}" target="_blank"><span class="panel-title">{{ __('Smokeping') }} <i class="glyphicon glyphicon-share-alt"></i></span></a>
                @else
                    <span class="panel-title">{{ __('Smokeping') }}</span>
                @endif

                <ul class="nav nav-tabs" style="display: inline-block">
                    @foreach($data['smokeping_tabs'] as $tab)
                        <li @if($loop->first) class="active" @endif><a href="#{{ $tab }}" data-toggle="tab">{{ __('smokeping.' . $tab) }}</a></li>
                    @endforeach
                </ul>
            </x-slot>

            <div class="tab-content">
                @foreach($data['smokeping_tabs'] as $direction)
                    <div class="tab-pane fade in @if($loop->first) active @endif" id="{{ $direction }}">
                        <x-graph-row :type="'device_smokeping_' . $direction . '_all_avg'" title="Average" :device="$device" columns="responsive"></x-graph-row>
                    </div>
                    <div class="row"><x-graph-row :type="'device_smokeping_' . $direction . '_all'" title="Aggregate" :device="$device" columns="responsive"></x-graph-row>
                        @foreach($data['smokeping']->otherGraphs($direction) as $info)
                            <x-graph-row :type="$info['graph']" :device="$info['device']" columns="responsive">
                                <x-slot name="title"><x-device-link device="$info['device']" /></x-slot>
                            </x-graph-row>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </x-panel>
    @endif
    <x-panel title="{{ __('ICMP Performance') }}">
        <x-graph-row type="device_icmp_perf" :device="$device" columns="responsive"></x-graph-row>
    </x-panel>
</x-device.page>
@endsection

@push('scripts')
    <script type="text/javascript">
        $(function () {
            $("#dtpickerfrom").datetimepicker({
                useCurrent: true,
                sideBySide: true,
                useStrict: false,
                icons: {
                    time: 'fa fa-clock-o',
                    date: 'fa fa-calendar',
                    up: 'fa fa-chevron-up',
                    down: 'fa fa-chevron-down',
                    previous: 'fa fa-chevron-left',
                    next: 'fa fa-chevron-right',
                    today: 'fa fa-calendar-check-o',
                    clear: 'fa fa-trash-o',
                    close: 'fa fa-close'
                }
            });
            $("#dtpickerto").datetimepicker({
                useCurrent: true,
                sideBySide: true,
                useStrict: false,
                icons: {
                    time: 'fa fa-clock-o',
                    date: 'fa fa-calendar',
                    up: 'fa fa-chevron-up',
                    down: 'fa fa-chevron-down',
                    previous: 'fa fa-chevron-left',
                    next: 'fa fa-chevron-right',
                    today: 'fa fa-calendar-check-o',
                    clear: 'fa fa-trash-o',
                    close: 'fa fa-close'
                }
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .panel.with-nav-tabs .panel-title {
            vertical-align: top; /* otherwise pushes tabs off bottom */
            margin: 10px;
            display: inline-block;
        }

        .panel.with-nav-tabs .panel-heading {
            padding: 5px 5px 0 5px;
        }

        .panel.with-nav-tabs .nav-tabs {
            border-bottom: none;
            margin-bottom: -5px;
        }

        .panel.with-nav-tabs .nav-tabs > li > a {
            padding-right: 10px;
        }

        .panel.with-nav-tabs .nav-justified {
            margin-bottom: -1px;
        }

        .bootstrap-datetimepicker-widget.dropdown-menu {
            inset: auto!important;
        }
    </style>
@endpush
