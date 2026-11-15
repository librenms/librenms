@extends('layouts.librenmsv1')

@section('title', __('PeeringDB'))

@section('content')
<div class="container-fluid">
    <x-panel title="{{ __('PeeringDB') }}">
        @if($cached)
            <p class="text-muted">{{ __('Cached date: :date', ['date' => $cached->format('Y-m-d H:i:s')]) }}</p>
        @endif
        <x-slot:table>
            <table class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>{{ __('ASN') }}</th>
                    <th>{{ __('AS Name') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($asns as $asn => $name)
                    <tr>
                        <td>{{ $asn }}</td>
                        <td>{{ $name }}</td>
                        <td class="text-right">
                            <a class="btn btn-sm btn-primary" href="{{ route('peering.exchanges', $asn) }}">{{ __('Show connected IXes') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center"><em>{{ __('No public ASNs found on your devices.') }}</em></td></tr>
                @endforelse
                </tbody>
            </table>
        </x-slot:table>
    </x-panel>
</div>
@endsection
