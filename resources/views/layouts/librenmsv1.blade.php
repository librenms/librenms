<!DOCTYPE HTML>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $pagetitle }}</title>
    <base href="{{ LibreNMS\Config::get('base_url') }}">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!LibreNMS\Config::get('favicon', false))
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" href="{{ asset('images/favicon-32x32.png') }}" sizes="32x32">
        <link rel="icon" type="image/png" href="{{ asset('images/favicon-16x16.png') }}" sizes="16x16">
        <link rel="mask-icon" href="{{ asset('images/safari-pinned-tab.svg') }}" color="#5bbad5">
        <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">
    @else
        <link rel="shortcut icon" href="{{ LibreNMS\Config::get('favicon') }}">
    @endif

    <link rel="manifest" href="{{ asset('images/manifest.json') }}" crossorigin="use-credentials">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="msapplication-config" content="{{ asset('images/browserconfig.xml') }}">
    <meta name="theme-color" content="#ffffff">

    <link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-switch.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/toastr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/jquery.bootgrid.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tagmanager.css') }}" rel="stylesheet">
    <link href="{{ asset('css/mktree.css') }}" rel="stylesheet">
    <link href="{{ asset('css/vis.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/fontawesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/v4-shims.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/jquery.gridster.min.css?ver=09292021') }}" rel="stylesheet">
    <link href="{{ asset('css/leaflet.css') }}" rel="stylesheet">
    <link href="{{ asset('css/MarkerCluster.css') }}" rel="stylesheet">
    <link href="{{ asset('css/MarkerCluster.Default.css') }}" rel="stylesheet">
    <link href="{{ asset('css/L.Control.Locate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/leaflet.awesome-markers.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2-bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/query-builder.default.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset(LibreNMS\Config::get('stylesheet', 'css/styles.css')) }}?ver=20230928" rel="stylesheet">
    <link href="{{ asset('css/' . LibreNMS\Config::get('applied_site_style', 'light') . '.css?ver=632417643') }}" rel="stylesheet">
    @foreach(LibreNMS\Config::get('webui.custom_css', []) as $custom_css)
        <link href="{{ $custom_css }}" rel="stylesheet">
    @endforeach
    @yield('css')
    @stack('styles')

    <script src="{{ asset('js/polyfill.min.js') }}"></script>
    <script src="{{ asset('js/alpine.min.js') }}" defer></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/jquery.min.js?ver=05072021') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js?ver=05072021') }}"></script>
    <script src="{{ asset('js/bootstrap-hover-dropdown.min.js?ver=05072021') }}"></script>
    <script src="{{ asset('js/bootstrap-switch.min.js?ver=05072021') }}"></script>
    <script src="{{ asset('js/hogan-2.0.0.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datetimepicker.min.js?ver=05072021') }}"></script>
    <script src="{{ asset('js/typeahead.bundle.min.js?ver=05072021') }}"></script>
    <script src="{{ asset('js/tagmanager.js?ver=05072021') }}"></script>
    <script src="{{ asset('js/mktree.js') }}"></script>
    <script src="{{ asset('js/jquery.bootgrid.min.js') }}"></script>
    <script src="{{ asset('js/handlebars.min.js') }}"></script>
    <script src="{{ asset('js/pace.min.js') }}"></script>
    <script src="{{ asset('js/qrcode.min.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var ajax_url = "{{ url('/ajax') }}";
    </script>
    <script src="{{ asset('js/librenms.js?ver=31012024') }}"></script>
    <script type="text/javascript" src="{{ asset('js/overlib_mini.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/flasher.min.js?ver=0.6.1') }}"></script>
    <script type="text/javascript" src="{{ asset('js/toastr.min.js?ver=05072021') }}"></script>
    <script type="text/javascript" src="{{ asset('js/boot.js?ver=10272021') }}"></script>
    <script>
        // Apply color scheme
        if ('{{ LibreNMS\Config::get('applied_site_style') }}' === 'dark') {
            document.documentElement.classList.add('tw-dark')
        } else {
            document.documentElement.classList.remove('tw-dark')
        }
    </script>
    @auth
        @if(session('preferences.timezone_static') == null || ! session('preferences.timezone_static'))
        <script>
            var tz = window.Intl.DateTimeFormat().resolvedOptions().timeZone;
            if(tz !== '{{ session('preferences.timezone') }}') {
                updateTimezone(tz, false);
            }
        </script>
        @endif
        <script src="{{ asset('js/register-service-worker.js') }}" defer></script>
    @endauth
    @yield('javascript')
</head>
<body>
@if(Auth::check())
    <script>updateResolution();</script>
@endif

@if(Request::get('bare') == 'yes')
    <style>body { padding-top: 0 !important; padding-bottom: 0 !important; }</style>
@elseif($show_menu)
    @include('layouts.menu')
@endif

<br />

@yield('content')

@yield('scripts')

@flasher_render

@stack('scripts')
</body>
</html>
