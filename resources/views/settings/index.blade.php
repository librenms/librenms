@extends('layouts.librenmsv1')

@section('title', __('Settings'))

@section('content')
    <div class="container">
        <div id="app">
            <librenms-settings
                prefix="{{ url('settings') }}"
                initial-tab="{{ $active_tab }}"
                initial-section="{{ $active_section }}"
                :groups="{{ $groups }}"
            ></librenms-settings>
        </div>
    </div>
@endsection

@push('styles')
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    @routes
    <script src="{{ mix('/js/manifest.js') }}"></script>
    <script src="{{ mix('/js/vendor.js') }}"></script>
    <script src="{{ mix('/js/app.js') }}"></script>
@endpush

