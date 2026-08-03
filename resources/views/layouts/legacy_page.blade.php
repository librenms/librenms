@extends('layouts.librenmsv1')

@section('content')
    @php
        // Prefer controller-provided host; fall back to request path so bind-mount smoke
        // stacks that only overlay resources/ still get presentation hosts.
        $lnmsLegacyHosts = [
            'alerts' => 'lnms-alerts',
            'alert-log' => 'lnms-alert-log',
            'alert-rules' => 'lnms-alert-rules',
            'eventlog' => 'lnms-eventlog',
            'syslog' => 'lnms-syslog',
            'bills' => 'lnms-bills',
            'bill' => 'lnms-bill',
        ];
        $lnmsLegacyHost = $legacy_host ?? '';
        if ($lnmsLegacyHost === '') {
            $lnmsLegacyHost = $lnmsLegacyHosts[request()->segment(1) ?? ''] ?? '';
        }
    @endphp
    <div class="container-fluid{{ $lnmsLegacyHost !== '' ? ' ' . $lnmsLegacyHost : '' }}">
        <div class="row">
            <div class="col-md-12">
                {!! $content !!}
            </div>
        </div>
    </div>
    <x-refresh-timer :refresh="$refresh"></x-refresh-timer>
@endsection
