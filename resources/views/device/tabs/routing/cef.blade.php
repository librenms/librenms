@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device" :subtitle="__('CEF')">
        <x-device.routing-tabs :device="$device" tab="cef" />

        <x-option-bar name="CEF" :options="$cef_options" :selected="$view" />

        <x-panel>
            <div class="table-responsive">
                <table class="table table-condensed table-hover tw:border-collapse">
                    <thead>
                        <tr>
                            <th><span title="{{ __('Physical hardware entity') }}">{{ __('Entity') }}</span></th>
                            <th><span title="{{ __('Address Family') }}">{{ __('AFI') }}</span></th>
                            <th><span title="{{ __('CEF Switching Path') }}">{{ __('Path') }}</span></th>
                            <th><span title="{{ __('Number of packets dropped.') }}">{{ __('Drop') }}</span></th>
                            <th><span title="{{ __('Number of packets that could not be switched in the normal path and were punted to the next-fastest switching vector.') }}">{{ __('Punt') }}</span></th>
                            <th><span title="{{ __('Number of packets that could not be switched in the normal path and were punted to the host.') }}">{{ __('Punt2Host') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($cef_rows as $cef)
                        <tr>
                            <td>{{ $cef['entity_descr'] }}</td>
                            <td>{{ $cef['afi'] }}</td>
                            <td>
                                @if($cef['path_title'])
                                    <span title="{{ $cef['path_title'] }}">{{ $cef['path'] }}</span>
                                @else
                                    {{ $cef['path'] }}
                                @endif
                            </td>
                            <td>
                                {{ $cef['drop'] }}
                                @if($cef['drop_rate'] !== null)
                                    <span class="tw:text-red-600 dark:tw:text-red-400">({{ $cef['drop_rate'] }}/sec)</span>
                                @endif
                            </td>
                            <td>
                                {{ $cef['punt'] }}
                                @if($cef['punt_rate'] !== null)
                                    <span class="tw:text-red-600 dark:tw:text-red-400">({{ $cef['punt_rate'] }}/sec)</span>
                                @endif
                            </td>
                            <td>
                                {{ $cef['punt2host'] }}
                                @if($cef['punt2host_rate'] !== null)
                                    <span class="tw:text-red-600 dark:tw:text-red-400">({{ $cef['punt2host_rate'] }}/sec)</span>
                                @endif
                            </td>
                        </tr>

                        @if($view === 'graphs')
                            <tr>
                                <td colspan="6">
                                    <x-graph-row :type="'cefswitching_graph'" :vars="['id' => $cef['id']]" />
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="tw:text-center tw:p-5">
                                <em>{{ __('No CEF switching entries found for this device.') }}</em>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-panel>
    </x-device.page>
@endsection
