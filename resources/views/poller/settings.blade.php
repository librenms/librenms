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

@push('styles')
    <link href="{{ asset(mix('/css/vendor.css')) }}" rel="stylesheet">
@endpush

@section('javascript')
    <script src="{{ asset(mix('/js/lang/en.js')) }}"></script>
    <script src="{{ asset(mix('/js/lang/' . app()->getLocale() . '.js')) }}"></script>
    <script src="{{ asset(mix('/js/manifest.js')) }}"></script>
    <script src="{{ asset(mix('/js/vendor.js')) }}"></script>
    @routes
@endsection

@push('scripts')
    <script src="{{ asset(mix('/js/app.js')) }}"></script>
@endpush
