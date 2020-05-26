@extends('poller.index')

@section('title', __('Poller Settings'))

@section('content')
    @parent
    <div id="app">
        @foreach($poller_cluster as $index => $poller)
            <poller-settings :settings='@json($settings[$index])' name="{{ $poller->poller_name }}" node_id="{{ $poller->node_id }}">
            </poller-settings>
        @endforeach
    </div>

@endsection

@push('styles')
    <link href="{{ asset(mix('/css/app.css')) }}" rel="stylesheet">
@endpush

@push('scripts')
    @routes
    <script src="{{ asset(mix('/js/manifest.js')) }}"></script>
    <script src="{{ asset(mix('/js/vendor.js')) }}"></script>
    <script src="{{ asset(mix('/js/app.js')) }}"></script>
@endpush
