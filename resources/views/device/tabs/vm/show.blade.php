@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <p>
            <a href="{{ route('device', ['device' => $device, 'tab' => 'vm']) }}">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                {{ __('All virtual machines') }}
            </a>
        </p>

        <x-panel title="{{ __('Virtual Machine') }}">
            <div class="table-responsive">
                <table class="table table-condensed table-striped">
                    <tbody>
                    <tr>
                        <th scope="row">{{ __('VM Name') }}</th>
                        <td>
                            {{ $vm->vmwVmDisplayName }}
                            @if($vm->parentDevice)
                                (<x-device-link :device="$vm->parentDevice" />)
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('Power Status') }}</th>
                        <td><span class="label {{ $vm->stateLabel[1] }}">{{ $vm->stateLabel[0] }}</span></td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('Type') }}</th>
                        <td>{{ $vm->vm_type }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('Operating System') }}</th>
                        <td>{{ $vm->operatingSystem }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('Memory') }}</th>
                        <td>{{ $vm->memoryFormatted }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ __('vCPUs') }}</th>
                        <td>{{ $vm->vmwVmCpus }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </x-panel>

        @forelse($graphs as $graph)
            <x-graph-row
                loading="lazy"
                columns="responsive"
                :type="$graph['type']"
                :title="$graph['title']"
                :vars="['id' => $vm->id]"
            ></x-graph-row>
        @empty
            <p class="text-muted">{{ __('No graphs have been recorded for this virtual machine.') }}</p>
        @endforelse
    </x-device.page>
@endsection
