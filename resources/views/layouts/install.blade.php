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
    <style type="text/css">
        .primary-panel {
            box-shadow: 0 0 20px black;
        }

        body {
            background-color: #047396;
        }

        .btn-circle {
            width: 60px;
            height: 60px;
            padding: 8px 14px;
            border-radius: 30px;
            font-size: 24px;
            line-height: 1.7;
            box-shadow: 2px 2px 4px grey;
        }

        .content-divider {
            padding-top: 20px;
            border-bottom: 1px solid #f6f6f6;
            margin-bottom: 20px;
        }
    </style>
    @yield('style')
</head>
<body>
<div class="container">
    <div class="panel panel-default col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-xs-12 primary-panel">
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-10 col-xs-offset-1">
                    <h2 class="text-center">
                        <img src="{{ asset(\LibreNMS\Config::get('title_image', "images/librenms_logo_" . \LibreNMS\Config::get('applied_site_style') . ".svg")) }}" alt="{{ \LibreNMS\Config::get('project_name', 'LibreNMS') }}">
                        @yield('title')
                    </h2>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-2 col-xs-offset-1">
                    <a href="{{ route('install.checks') }}"
                       class="btn btn-primary btn-circle"
                       title="@lang('install.checks.title')">
                        <i class="fa fa-lg fa-list-ul fa-flip-horizontal"></i>
                    </a>
                </div>
                <div class="col-xs-2">
                    <a href="{{ route('install.database') }}"
                       class="btn btn-primary btn-circle @if(!session('install.checks')) disabled @endif"
                       title="@lang('install.database.title')">
                        <i class="fa fa-lg fa-database"></i>
                    </a>
                </div>
                <div class="col-xs-2">
                    <a href="{{ route('install.migrate') }}"
                       class="btn btn-primary btn-circle @if(!session('install.database')) disabled @endif"
                       title="@lang('install.migrate.title')">
                        <i class="fa fa-lg fa-repeat"></i>
                    </a>
                </div>
                <div class="col-xs-2">
                    <a href="{{ route('install.user') }}"
                       class="btn btn-primary btn-circle @if(!session('install.migrate')) disabled @endif"
                       title="@lang('install.user.title')">
                        <i class="fa fa-lg fa-key"></i>
                    </a>
                </div>
                <div class="col-xs-2">
                    <a href="{{ route('install.finish') }}"
                       class="btn btn-primary btn-circle @if(!session('install.user')) disabled @endif"
                       title="@lang('install.finish.title')">
                        <i class="fa fa-lg fa-check"></i>
                    </a>
                </div>
            </div>
            <div class="content-divider"></div>
            <div class="row">
                <div id="error-box" class="col-xs-12">
                    @if(!empty($message))
                        <div class="alert alert-danger">{{ $message }}</div>
                    @endif
                </div>
            </div>
            @yield('content')
        </div>
    </div>
</div>
@yield('scripts')
</body>
</html>
