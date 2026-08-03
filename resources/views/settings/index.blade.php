@extends('layouts.librenmsv1')

@section('title', __('settings.title'))

@section('content')
    {{-- Phase 5 host: .lnms-settings — Ziggy @routes, Vue save/list, settings.view|update gates unchanged --}}
    <div class="container-fluid lnms-settings">
        <h1 class="sr-only">{{ __('settings.title') }}</h1>
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
    @routes
    @vuei18n
@endpush
