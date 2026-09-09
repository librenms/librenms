@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-option-bar :name="__('Munin Plugins')" :options="$data['categoryOptions']" :selected="$data['currentGroup']"></x-option-bar>

    @forelse($data['plugins'] as $plugin)
        <x-panel :title="$plugin->mplug_title . ' (' . $plugin->mplug_type . ')'">
            <x-graph-row
                type="munin_graph"
                :device="$device"
                :vars="['plugin' => $plugin->mplug_type]"
                columns="responsive"
                loading="lazy"
            ></x-graph-row>
        </x-panel>
    @empty
        <div class="tw:text-center tw:text-gray-500 tw:py-5">
            <em>{{ __('No Munin plugins found for this category.') }}</em>
        </div>
    @endforelse
</x-device.page>
@endsection
