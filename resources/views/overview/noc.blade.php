@extends('layouts.librenmsv1')

@section('title', __('dashboard.noc.title'))

@section('content')
<div class="container-fluid" style="padding: 0;">
    <div class="row" style="margin: 0;">
        <div class="col-md-12" style="padding: 0;">
            @if($noc_dashboards->isEmpty())
                <div class="alert alert-info" style="margin: 15px;">{{ __('dashboard.noc.empty') }}</div>
            @else
                <div id="noc-viewport" style="margin-bottom: 0;">
                    <div id="noc-topbar" style="display: flex; align-items: center; gap: 12px;">
                        <strong id="noc-current-name">{{ $noc_dashboards->first()->dashboard_name }}</strong>
                        <div id="noc-topbar-controls" style="display: flex; align-items: center; gap: 10px;">
                            <span id="noc-countdown" style="font-size: 12px; color: inherit;">{{ __('dashboard.noc.next_in') }}: <span id="noc-countdown-value"></span>s</span>
                            <button id="noc-fullscreen-btn" type="button" class="btn btn-sm btn-primary">{{ __('dashboard.noc.fullscreen') }}</button>
                        </div>
                    </div>
                    <div style="padding: 0;">
                        <iframe id="noc-dashboard-frame"
                            title="NOC Dashboard"
                            style="width: 100%; height: calc(100vh - 220px); border: 0; pointer-events: none;"
                            src="{{ route('overview', ['dashboard' => $noc_dashboards->first()->dashboard_id, 'bare' => 'yes']) }}"></iframe>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #noc-topbar {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        min-height: 42px;
        padding: 8px 12px;
        background-color: #f5f5f5;
        border-bottom: 1px solid #ddd;
    }

    .dark #noc-topbar {
        background-color: #3e444c;
        border-bottom-color: rgba(0, 0, 0, 0.6);
    }

    #noc-current-name {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        text-align: center;
        display: block;
        margin: 0;
        font-size: 1.2em;
        white-space: nowrap;
        max-width: calc(100% - 180px);
        overflow: hidden;
        text-overflow: ellipsis;
        pointer-events: none;
    }

    #noc-topbar-controls {
        margin-left: auto;
    }

    #noc-countdown-value {
        display: inline-block;
        min-width: 2ch;
        text-align: right;
        font-variant-numeric: tabular-nums;
        font-feature-settings: "tnum" 1;
    }

    #noc-viewport::backdrop {
        background-color: #fff;
    }

    .dark #noc-viewport::backdrop {
        background-color: #272b30;
    }

    #noc-viewport:fullscreen,
    #noc-viewport:-webkit-full-screen {
        display: flex;
        flex-direction: column;
        width: 100vw;
        height: 100vh;
        margin: 0;
        border: 0;
        border-radius: 0;
        background-color: #fff;
    }

    .dark #noc-viewport:fullscreen,
    .dark #noc-viewport:-webkit-full-screen {
        background-color: #272b30;
    }

    #noc-viewport:fullscreen #noc-topbar,
    #noc-viewport:-webkit-full-screen #noc-topbar {
        background-color: #f5f5f5;
        flex-shrink: 0;
    }

    .dark #noc-viewport:fullscreen #noc-topbar,
    .dark #noc-viewport:-webkit-full-screen #noc-topbar {
        background-color: #3e444c;
    }

    #noc-viewport:fullscreen #noc-topbar-controls,
    #noc-viewport:-webkit-full-screen #noc-topbar-controls {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
    }

    #noc-viewport:fullscreen > div:last-child,
    #noc-viewport:-webkit-full-screen > div:last-child {
        flex: 1;
        min-height: 0;
    }

    #noc-viewport:fullscreen #noc-dashboard-frame,
    #noc-viewport:-webkit-full-screen #noc-dashboard-frame {
        width: 100%;
        height: 100% !important;
    }
</style>
@endpush

@push('scripts')
<script>
    @if($noc_dashboards->isNotEmpty())
    var nocDashboards = @json($noc_dashboards->map(fn ($dashboard) => [
        'id' => $dashboard->dashboard_id,
        'name' => $dashboard->dashboard_name,
    ])->values());
    var nocRotateSeconds = {{ (int) $rotate_seconds }};
    var nocCurrentIndex = 0;
    var nocCountdown = nocRotateSeconds;

    function nocDashboardUrl(dashboardId) {
        return '{{ route('overview') }}' + '?dashboard=' + dashboardId + '&bare=yes';
    }

    function updateCountdown() {
        $('#noc-countdown-value').text(nocCountdown);
    }

    function setNocDashboard(index) {
        var dashboard = nocDashboards[index];
        if (! dashboard) {
            return;
        }

        $('#noc-current-name').text(dashboard.name);
        $('#noc-dashboard-frame').attr('src', nocDashboardUrl(dashboard.id));
        nocCountdown = nocRotateSeconds;
        updateCountdown();
    }

    setInterval(function () {
        nocCountdown--;
        if (nocCountdown <= 0) {
            if (nocDashboards.length > 1) {
                nocCurrentIndex = (nocCurrentIndex + 1) % nocDashboards.length;
                setNocDashboard(nocCurrentIndex);
            }
            nocCountdown = nocRotateSeconds;
        }
        updateCountdown();
    }, 1000);

    updateCountdown();

    function updateFullscreenButtonText() {
        var button = $('#noc-fullscreen-btn');
        if (! button.length) {
            return;
        }

        button.text(document.fullscreenElement ? '{{ __('dashboard.noc.exit_fullscreen') }}' : '{{ __('dashboard.noc.fullscreen') }}');
    }

    $('#noc-fullscreen-btn').on('click', function () {
        var viewport = document.getElementById('noc-viewport');

        if (document.fullscreenElement) {
            document.exitFullscreen();
            return;
        }

        if (viewport && viewport.requestFullscreen) {
            viewport.requestFullscreen().catch(function () {
                toastr.error('{{ __('dashboard.noc.fullscreen_error') }}');
            });
        }
    });

    document.addEventListener('fullscreenchange', updateFullscreenButtonText);
    updateFullscreenButtonText();
    @endif
</script>
@endpush
