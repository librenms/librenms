@extends('poller.index')

@section('title', __('Poller Settings'))

@section('content')
    @parent
    <div id="app">
        <poller-settings
            :pollers='@json($poller_cluster, JSON_FORCE_OBJECT|JSON_HEX_APOS)'
            :settings='@json($settings, JSON_FORCE_OBJECT|JSON_HEX_APOS)'
        ></poller-settings>
    </div>
@endsection

@push('scripts')
    @routes
    @vuei18n
@endpush
