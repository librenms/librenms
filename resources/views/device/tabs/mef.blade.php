@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <x-panel title="{{ __('Metro Ethernet') }}">
        <x-slot:table>
            <table class="table table-hover table-condensed table-striped tw:mb-0!">
                <thead>
                    <tr>
                        <th>{{ __('Link') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('MTU') }}</th>
                        <th>{{ __('Admin State') }}</th>
                        <th>{{ __('Row State') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($data['mef_rows'] as $mef)
                    <tr>
                        <td>{{ $mef->mefIdent }}</td>
                        <td>{{ $mef->mefType }}</td>
                        <td>{{ $mef->mefMTU }}</td>
                        <td>
                            @if($mef->mefAdmState === 'unlocked')
                                <i class="fa fa-unlock fa-lg tw:text-green-600" aria-hidden="true" title="{{ __('Unlocked') }}"></i>
                            @else
                                <i class="fa fa-lock fa-lg tw:text-red-600" aria-hidden="true" title="{{ __('Locked') }}"></i>
                            @endif
                        </td>
                        <td>
                            @if($mef->mefRowState === 'active')
                                <span class="label label-success tw:inline-block tw:min-w-10">active</span>
                            @else
                                <span class="label label-default tw:inline-block tw:min-w-10">{{ $mef->mefRowState }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="tw:p-5 tw:text-center">
                            <em>{{ __('No Metro Ethernet connections found for this device.') }}</em>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </x-slot:table>
    </x-panel>
</x-device.page>
@endsection
