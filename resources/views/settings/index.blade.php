@extends('layouts.librenmsv1')

@section('title', __('settings.title'))

@section('content')
    <div class="container-fluid">
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

@push('scripts')
    @vuei18n
@endpush
