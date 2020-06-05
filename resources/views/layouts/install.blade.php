<!DOCTYPE HTML>
<html lang="en">
<head>
    <title>@lang('install.title')</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset(\LibreNMS\Config::get('stylesheet')) }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet" type="text/css"/>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-hover-dropdown.min.js') }}"></script>
    <script src="{{ asset('js/hogan-2.0.0.js') }}"></script>
</head>
<body style="background-color: #047396">
<div class="container">
    <div class="panel panel-default col-md-6 col-md-offset-3" style="box-shadow: 0 0 20px black;">
        <div class="panel-body">
            <div class="row" style="border-bottom: 1px solid #f6f6f6;">
                <div class="col-xs-10 col-xs-offset-1">
                    <h2 class="text-center">
                        <img src="{{ asset(\LibreNMS\Config::get('title_image', "images/librenms_logo_" . \LibreNMS\Config::get('applied_site_style') . ".svg")) }}" alt="{{ \LibreNMS\Config::get('project_name', 'LibreNMS') }}">
                        @lang('install.install')
                        </h2>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <h4 class="text-center">@lang('install.stage', ['stage' => $stage ?? 0, 'stages' => $stages ?? 6])</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div id="install-progress" class="progress progress-striped">
                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ !empty($stage) ? (($stages ?? 6) / $stage) : 0 }}"
                             aria-valuemin="0" aria-valuemax="100" style="width: {{ !empty($stage) ? (($stages ?? 6) / $stage) : 0 }}%">
                            <span class="sr-only">{{ !empty($stage) ? (($stages ?? 6) / $stage) : 0 }}% Complete</span>
                        </div>
                    </div>
                </div>
            </div>
            @if(!empty($msg))
                <div class="row">
                    <div class="col-xs-12">
                        <div class="alert alert-danger">{{ $msg }}</div>
                    </div>
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
