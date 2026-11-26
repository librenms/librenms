@extends('layouts.librenmsv1')

@section('title', __('PeeringDB'))

@section('content')
<div class="container-fluid">
    <x-panel>
        <x-slot:heading class="tw:flex tw:items-center tw:justify-between tw:flex-wrap tw:gap-2">
            <h3 class="panel-title">
                <a href="{{ route('peering.index') }}">{{ __('PeeringDB') }}</a>
                &raquo; <a href="{{ route('peering.exchanges', $asn) }}">AS{{ $asn }}</a>
                &raquo; {{ $exchange->name }}
            </h3>
            <form method="GET" class="form-inline tw:flex tw:items-center tw:gap-2">
                <select name="status" class="form-control input-sm">
                    <option value="all" @selected($status === 'all')>{{ __('All') }}</option>
                    <option value="connected" @selected($status === 'connected')>{{ __('Connected') }}</option>
                    <option value="unconnected" @selected($status === 'unconnected')>{{ __('Not connected') }}</option>
                </select>
                <input type="search" name="search" value="{{ $search }}" class="form-control input-sm" placeholder="{{ __('Search') }}">
                <button type="submit" class="btn btn-default btn-sm">{{ __('Filter') }}</button>
            </form>
        </x-slot:heading>
        <x-slot:table>
            <table class="table table-hover table-condensed table-striped">
                <thead>
                <tr>
                    <th>{{ __('ASN') }}</th>
                    <th>{{ __('IP') }}</th>
                    <th>{{ __('Peer') }}</th>
                    <th>{{ __('Connected') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($peers as $peer)
                    <tr>
                        <td>{{ $peer->remote_asn }}</td>
                        <td>{{ $peer->remote_ipaddr4 }}</td>
                        <td>{{ $peer->name }}</td>
                        <td>
                            @if($connected->has($peer->remote_ipaddr4))
                                <i class="fa fa-check fa-lg text-success" title="{{ __('Connected') }}"></i>
                            @else
                                <i class="fa fa-times fa-lg text-muted" title="{{ __('Not connected') }}"></i>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="https://www.peeringdb.com/asn/{{ $peer->remote_asn }}" target="_blank" rel="noopener" title="PeeringDB"><i class="fa fa-database" aria-hidden="true"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center"><em>{{ __('No peers found.') }}</em></td></tr>
                @endforelse
                </tbody>
            </table>
            <x-pagination-footer :paginator="$peers" :options="$perPageOptions" :per-page="$perPage" />
        </x-slot:table>
    </x-panel>
</div>
@endsection
