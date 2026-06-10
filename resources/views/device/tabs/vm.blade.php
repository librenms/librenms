@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        @isset($data['submenu'])
            <x-submenu :title="$title" :menu="$data['submenu']" :device-id="$device_id" :current-tab="$current_tab" :selected="$vars" />
        @endisset

        <table class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th>{{ __('VM Name') }}</th>
                <th>{{ __('Power Status') }}</th>
                <th>{{ __('Operating System') }}</th>
                <th>{{ __('Memory') }}</th>
                <th>{{ __('CPU') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['vms'] as $vm)
            <tr>
                <td>
                    @if ($vm->parentDevice)
                        <x-device-link :device="$vm->parentDevice" />
                    @else
                        {{ $vm->vmwVmDisplayName }}
                    @endif
                </td>
                <td>
                    <span style="min-width:40px; display:inline-block;" class="label {{ $vm->stateLabel[1] }}">{{ $vm->stateLabel[0] }}</span>
                </td>
                <td>{{ $vm->operatingSystem }}</td>
                <td>{{ $vm->memoryFormatted }}</td>
                <td>{{ $vm->vmwVmCpus }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</x-device.page>
@endsection



