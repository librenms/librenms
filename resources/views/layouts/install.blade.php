<!DOCTYPE HTML>
<html lang="en">
<head>
    <title>@lang('install.title')</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset(\LibreNMS\Config::get('stylesheet')) }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet" type="text/css"/>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap4.min.js') }}"></script>
    <style type="text/css">
        .primary-panel {
            box-shadow: 0 0 25px #333;
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
    <div class="card col-lg-8 offset-lg-2 col-md-10 offset-md-1 col-12 primary-panel">
        <div class="card-header text-center">
            <img class="card-img-top" src="{{ asset(\LibreNMS\Config::get('title_image', "images/librenms_logo_" . \LibreNMS\Config::get('applied_site_style') . ".svg")) }}" alt="LibreNMS">
            <span class="h2">@yield('title')</span>
        </div>
        <div class="card-body">
            <div class="d-flex flex-row justify-content-around">
                <div>
                    <a href="{{ route('install.checks') }}"
                       id="install-checks-button"
                       class="btn btn-primary btn-circle"
                       title="@lang('install.checks.title')">
                        <i class="fa fa-lg fa-list-ul fa-flip-horizontal"></i>
                    </a>
                </div>
                <div>
                    <a href="{{ route('install.database') }}"
                       id="install-database-button"
                       class="btn btn-primary btn-circle @if(!session('install.checks')) disabled @endif"
                       title="@lang('install.database.title')">
                        <i class="fa fa-lg fa-database"></i>
                    </a>
                </div>
                <div>
                    <a href="{{ route('install.migrate') }}"
                       id="install-migrate-button"
                       class="btn btn-primary btn-circle @if(!session('install.database')) disabled @endif"
                       title="@lang('install.migrate.title')">
                        <i class="fa fa-lg fa-repeat"></i>
                    </a>
                </div>
                <div>
                    <a href="{{ route('install.user') }}"
                       id="install-user-button"
                       class="btn btn-primary btn-circle @if(!session('install.migrate')) disabled @endif"
                       title="@lang('install.user.title')">
                        <i class="fa fa-lg fa-key"></i>
                    </a>
                </div>
                <div>
                    <a href="{{ route('install.finish') }}"
                       id="install-finish-button"
                       class="btn btn-primary btn-circle @if(!session('install.user')) disabled @endif"
                       title="@lang('install.finish.title')">
                        <i class="fa fa-lg fa-check"></i>
                    </a>
                </div>
            </div>
            <div class="content-divider"></div>
            <div class="row">
                <div id="error-box" class="col-12">
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
