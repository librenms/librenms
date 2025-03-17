@extends('poller.index')

@section('title', __('Poller Settings'))

@section('content')
    @parent
    <div id="app">
        <poller-settings
            :pollers='@json($poller_cluster, JSON_FORCE_OBJECT)'
            :settings='@json($settings, JSON_FORCE_OBJECT)'
        ></poller-settings>
    </div>
@endsection

@push('scripts')
    @vuei18n
@endpush
