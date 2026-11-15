@extends('layouts.librenmsv1')

@section('title', __('PeeringDB'))

@section('content')
<div class="container-fluid">
    <x-panel>
        <x-slot:heading>
            <h3 class="panel-title"><a href="{{ route('peering.index') }}">{{ __('PeeringDB') }}</a> &raquo; AS{{ $asn }}</h3>
        </x-slot:heading>
        <x-slot:table>
            <table class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>{{ __('Exchange') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($exchanges as $exchange)
                    <tr>
                        <td>{{ $exchange->name }}</td>
                        <td class="text-right">
                            <a class="btn btn-sm btn-primary" href="{{ route('peering.peers', [$asn, $exchange->ix_id]) }}">{{ __('Show Peers') }}</a>
                            <a href="https://www.peeringdb.com/ix/{{ $exchange->ix_id }}" target="_blank" rel="noopener" title="PeeringDB"><i class="fa fa-database" aria-hidden="true"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-center"><em>{{ __('No exchanges found.') }}</em></td></tr>
                @endforelse
                </tbody>
            </table>
        </x-slot:table>
    </x-panel>
</div>
@endsection
