<!DOCTYPE HTML>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $pagetitle }}</title>
    <base href="{{ LibreNMS\Config::get('base_url') }}" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!LibreNMS\Config::get('favicon', false))
        <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
        <link rel="icon" type="image/png" href="images/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="images/favicon-16x16.png" sizes="16x16">
        <link rel="manifest" href="images/manifest.json">
        <link rel="mask-icon" href="images/safari-pinned-tab.svg" color="#5bbad5">
        <link rel="shortcut icon" href="images/favicon.ico">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="msapplication-config" content="images/browserconfig.xml">
        <meta name="theme-color" content="#ffffff">
    @else
        <link rel="shortcut icon" href="{{ LibreNMS\Config::get('favicon') }}" />
    @endif

    <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
    <link href="css/toastr.min.css" rel="stylesheet" type="text/css" />
    <link href="css/jquery-ui.min.css" rel="stylesheet" type="text/css" />
    <link href="css/jquery.bootgrid.min.css" rel="stylesheet" type="text/css" />
    <link href="css/tagmanager.css" rel="stylesheet" type="text/css" />
    <link href="css/mktree.css" rel="stylesheet" type="text/css" />
    <link href="css/vis.min.css" rel="stylesheet" type="text/css" />
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="css/jquery.gridster.min.css" rel="stylesheet" type="text/css" />
    <link href="css/leaflet.css" rel="stylesheet" type="text/css" />
    <link href="css/MarkerCluster.css" rel="stylesheet" type="text/css" />
    <link href="css/MarkerCluster.Default.css" rel="stylesheet" type="text/css" />
    <link href="css/L.Control.Locate.min.css" rel="stylesheet" type="text/css" />
    <link href="css/leaflet.awesome-markers.css" rel="stylesheet" type="text/css" />
    <link href="css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="css/query-builder.default.min.css" rel="stylesheet" type="text/css" />
    <link href="{{ LibreNMS\Config::get('stylesheet', 'css/styles.css') }}?ver=20181128" rel="stylesheet" type="text/css" />
    <link href="css/{{ LibreNMS\Config::get('site_style', 'light') }}.css?ver=632417642" rel="stylesheet" type="text/css" />
    @foreach(LibreNMS\Config::get('webui.custom_css', []) as $custom_css)
        <link href="{{ $custom_css }}" rel="stylesheet" type="text/css" />
    @endforeach
    @yield('css')

    <script src="js/polyfill.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootstrap-hover-dropdown.min.js"></script>
    <script src="js/bootstrap-switch.min.js"></script>
    <script src="js/hogan-2.0.0.js"></script>
    <script src="js/jquery.cycle2.min.js"></script>
    <script src="js/moment.min.js"></script>
    <script src="js/bootstrap-datetimepicker.min.js"></script>
    <script src="js/typeahead.bundle.min.js"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script src="js/tagmanager.js"></script>
    <script src="js/mktree.js"></script>
    <script src="js/jquery.bootgrid.min.js"></script>
    <script src="js/handlebars.min.js"></script>
    <script src="js/pace.min.js"></script>
    <script src="js/qrcode.min.js"></script>
    @if(LibreNMS\Config::get('enable_lazy_load', true))
        <script src="js/jquery.lazyload.min.js"></script>
        <script src="js/lazyload.js"></script>
    @endif
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/librenms.js?ver=20181130') }}"></script>
    <script type="text/javascript">

        <!-- Begin
        function popUp(URL)
        {
            day = new Date();
            id = day.getTime();
            eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=550,height=600');");
        }
        // End -->
    </script>
    <script type="text/javascript" src="js/overlib_mini.js"></script>
    <script type="text/javascript" src="js/toastr.min.js"></script>
    <script type="text/javascript" src="js/boot.js"></script>
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
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">

@yield('content')

        </div>
    </div>
</div>

{!! Toastr::render() !!}

</body>
</html>
