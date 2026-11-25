@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-panel>
        <x-slot:heading class="tw:flex tw:gap-2 tw:flex-wrap">
            <x-option-bar :options="$options" :selected="$view" border="none" class="tw:inline-block" />
            @if($channels)
                <x-option-bar :name="__('Channels')" :options="$channels" :selected="$channel" border="none" class="tw:inline-block" />
            @endif
        </x-slot:heading>
    </x-panel>

    @if($view == 'stats')
        <x-panel>
            <form method="get" action="{{ $form['action'] }}" class="form-inline">
                <input type="hidden" name="nfsen" value="stats">
                <label>{{ __('Top N') }}
                    <select name="topN" class="form-control input-sm">
                        @foreach($tops as $top)
                            <option value="{{ $top }}" @selected($top == $form['topN'])>{{ $top }}</option>
                        @endforeach
                    </select>
                </label>
                <label>{{ __('During the last') }}
                    <select name="lastN" class="form-control input-sm">
                        @foreach($lasts as $seconds => $text)
                            <option value="{{ $seconds }}" @selected($seconds == $form['lastN'])>{{ $text }}</option>
                        @endforeach
                    </select>
                </label>
                <label>{{ __('Stat Type') }}
                    <select name="stattype" class="form-control input-sm">
                        @foreach($types as $type => $text)
                            <option value="{{ $type }}" @selected($type == $form['stattype'])>{{ __($text) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>{{ __('Order By') }}
                    <select name="statorder" class="form-control input-sm">
                        @foreach($orders as $order)
                            <option value="{{ $order }}" @selected($order == $form['statorder'])>{{ $order }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" name="process" value="1" class="btn btn-primary btn-sm">{{ __('Process') }}</button>
            </form>
        </x-panel>
        @isset($output)
            <pre>{{ $output }}</pre>
        @endisset
    @else
        @if($view == 'channel')
            @if($filter)
                <x-panel :title="$channel . ' ' . __('Filter')">{{ $filter }}</x-panel>
            @endif
            @php($graphs = ['device_nfsen_channel_flows' => 'Flows', 'device_nfsen_channel_packets' => 'Packets', 'device_nfsen_channel_traffic' => 'Traffic'])
        @else
            @php($graphs = ['device_nfsen_flows' => 'Flows', 'device_nfsen_packets' => 'Packets', 'device_nfsen_traffic' => 'Traffic'])
        @endif
        @foreach($graphs as $type => $title)
            <x-panel :title="__($title)">
                <x-graph-row :type="$type" :device="$device" :vars="array_filter(['channel' => $channel])" columns="responsive" />
            </x-panel>
        @endforeach
    @endif
</x-device.page>
@endsection
