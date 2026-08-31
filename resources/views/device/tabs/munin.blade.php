@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <div class="panel panel-default">
        <div class="panel-heading" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <h3 class="panel-title" style="margin-right: 15px;">
                <i class="fa fa-pie-chart" aria-hidden="true"></i> {{ __('Munin Plugins') }}
            </h3>
            @if($data['categories']->isNotEmpty())
                <ul class="nav nav-pills" style="margin: 0;">
                    @foreach($data['categories'] as $category)
                        <li class="{{ $data['currentGroup'] === $category ? 'active' : '' }}">
                            <a href="{{ route('device', ['device' => $device, 'tab' => 'munin', 'vars' => 'group=' . $category]) }}">
                                {{ ucfirst($category) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="panel-body">
            @forelse($data['plugins'] as $plugin)
                <div class="panel panel-default" style="margin-bottom: 20px;">
                    <div class="panel-heading">
                        <h4 class="panel-title">{{ $plugin->mplug_title }} ({{ $plugin->mplug_type }})</h4>
                    </div>
                    <div class="panel-body text-center">
                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{ route('graphs', ['type' => 'munin_graph', 'device' => $device->device_id, 'plugin' => $plugin->mplug_type]) }}">
                                    <x-graph
                                        :device="$device"
                                        type="munin_graph"
                                        :plugin="$plugin->mplug_type"
                                        :height="150"
                                    />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted" style="padding: 20px;">
                    <em>{{ __('No Munin plugins found for this category.') }}</em>
                </div>
            @endforelse
        </div>
    </div>
</x-device.page>
@endsection
