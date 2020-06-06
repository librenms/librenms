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

        .btn-circle {
            width: 70px;
            height: 70px;
            padding: 10px 16px;
            border-radius: 35px;
            font-size: 24px;
            line-height: 1.9;
            box-shadow: 3px 3px 5px black;
        }

        .content-divider {
            padding-top: 20px;
            border-bottom: 1px solid #f6f6f6;
        }
    </style>
</head>
<body style="background-color: #047396;">
<div class="container">
    <div class="panel panel-default col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-xs-12 primary-panel">
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-10 col-xs-offset-1">
                    <h2 class="text-center">
                        <img src="{{ asset(\LibreNMS\Config::get('title_image', "images/librenms_logo_" . \LibreNMS\Config::get('applied_site_style') . ".svg")) }}" alt="{{ \LibreNMS\Config::get('project_name', 'LibreNMS') }}">
                        @lang('install.install')
                    </h2>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-2 col-xs-offset-1">
                    <a href="{{ route('install.checks') }}" class="btn btn-primary btn-circle" title="@lang('install.steps.checks')"><i class="fa fa-lg fa-fw fa-clipboard"></i></a>
                </div>
                <div class="col-xs-2">
                    <a href="{{ route('install.database') }}" class="btn btn-primary btn-circle" title="@lang('install.steps.database')"><i class="fa fa-lg fa-fw fa-database"></i></a>
                </div>
                <div class="col-xs-2">
                    <a href="{{ route('install.migrate-database') }}" class="btn btn-primary btn-circle" title="@lang('install.steps.migrate')"><i class="fa fa-lg fa-fw fa-mouse-pointer"></i></a>
                </div>
                <div class="col-xs-2">
                    <a href="{{ route('install.user') }}" class="btn btn-primary btn-circle" title="@lang('install.steps.user')"><i class="fa fa-lg fa-fw fa-user"></i></a>
                </div>
                <div class="col-xs-2">
                    <a href="{{ route('install.finish') }}" class="btn btn-primary btn-circle" title="@lang('install.steps.finish')"><i class="fa fa-lg fa-fw fa-check"></i></a>
                </div>
            </div>
            <div class="row content-divider">

            </div>
            {{--            <div class="row">--}}
            {{--                <div class="col-xs-12">--}}
            {{--                    <div id="install-progress" class="progress progress-striped">--}}
            {{--                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ ($stage ?? 0) / ($stages ?? 6) * 100 }}"--}}
            {{--                             aria-valuemin="0" aria-valuemax="100" style="width: {{ ($stage ?? 0) / ($stages ?? 6) * 100 }}%">--}}
            {{--                            <span class="sr-only">{{ ($stage ?? 0) / ($stages ?? 6) * 100 }}% Complete</span>--}}
            {{--                        </div>--}}
            {{--                    </div>--}}
            {{--                </div>--}}
            {{--            </div>--}}
            <div class="row">
                <div id="error-box" class="col-xs-12">
                    @if(!empty($msg))
                        <div class="alert alert-danger">{{ $msg }}</div>
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
