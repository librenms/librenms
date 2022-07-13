@extends('layouts.librenmsv1')

@section('title', __('settings.title'))

@section('content')
    <div class="container">
        <div id="app">
            <librenms-settings
                prefix="{{ url('settings') }}"
                initial-tab="{{ $active_tab }}"
                initial-section="{{ $active_section }}"
                :tabs="{{ $groups }}"
            ></librenms-settings>
        </div>
    </div>
@endsection

@push('styles')
    <link href="{{ asset(mix('/css/vendor.css')) }}" rel="stylesheet">
@endpush

@push('scripts')
    @routes
    <script src="{{ asset(mix('/js/lang/en.js')) }}"></script>
    <script src="{{ asset(mix('/js/lang/' . app()->getLocale() . '.js')) }}"></script>
    <script src="{{ asset(mix('/js/manifest.js')) }}"></script>
    <script src="{{ asset(mix('/js/vendor.js')) }}"></script>
    <script src="{{ asset(mix('/js/app.js')) }}"></script>
@endpush

