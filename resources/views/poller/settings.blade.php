@extends('poller.index')

@section('title', __('Poller Settings'))

@section('content')
    @parent
    <div id="app">
        @foreach($poller_cluster as $index => $poller)
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">@lang(':name Settings', ['name' => $poller->poller_name]) <small>({{ $poller->node_id }})</small></h3>
                </div>
                <div class="panel-body">
                    @foreach($settings[$index] as $setting)
                        <librenms-setting
                            prefix="poller"
                            :setting='@json($setting)'
                        ></librenms-setting>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

@endsection

@push('styles')
    <link href="{{ asset(mix('/css/app.css')) }}" rel="stylesheet">
@endpush

@push('scripts')
    @routes
    <script src="{{ asset(mix('/js/lang/' . app()->getLocale() . '.js')) }}"></script>
    <script src="{{ asset(mix('/js/manifest.js')) }}"></script>
    <script src="{{ asset(mix('/js/vendor.js')) }}"></script>
    <script src="{{ asset(mix('/js/app.js')) }}"></script>
@endpush
