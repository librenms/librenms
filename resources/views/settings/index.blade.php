@extends('layouts.librenmsv1')

@section('title', __('Settings'))

@section('content')
    <div class="container">
        <div id="app">
            <librenms-settings
                prefix="{{ url('settings') }}"
                initial-tab="{{ $active_tab }}"
                initial-section="{{ $active_section }}"
            ></librenms-settings>
        </div>
    </div>
@endsection

@push('styles')
    <link href="{{ mix('/css/app.css') }}?v=10132019" rel="stylesheet">
@endpush

@push('scripts')
    @routes
    <script src="{{ mix('/js/manifest.js') }}?v=10132019"></script>
    <script src="{{ mix('/js/vendor.js') }}?v=10132019"></script>
    <script src="{{ mix('/js/app.js') }}?v=10172019"></script>
@endpush

