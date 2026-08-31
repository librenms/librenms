@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{{ __('Metro Ethernet') }}</h3>
        </div>
        <div class="panel-body">
            <table class="table table-hover table-condensed table-striped">
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
                                <i class="fa fa-unlock fa-lg icon-theme" aria-hidden="true" style="color: green;" title="{{ __('Unlocked') }}"></i>
                            @else
                                <i class="fa fa-lock fa-lg icon-theme" aria-hidden="true" style="color: red;" title="{{ __('Locked') }}"></i>
                            @endif
                        </td>
                        <td>
                            @if($mef->mefRowState === 'active')
                                <span style="min-width: 40px; display: inline-block;" class="label label-success">active</span>
                            @else
                                <span style="min-width: 40px; display: inline-block;" class="label label-default">{{ $mef->mefRowState }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 20px;">
                            <em>{{ __('No Metro Ethernet connections found for this device.') }}</em>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-device.page>
@endsection
