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
            min-height: 540px;
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
        <div class="card-header text-center" style="margin: 0 -20px">
            <img class="card-img-top p-3" src="{{ asset(\LibreNMS\Config::get('title_image', "images/librenms_logo_" . \LibreNMS\Config::get('applied_site_style') . ".svg")) }}" alt="LibreNMS">
            <span class="h2">@yield('title')</span>
        </div>
        <div class="card-body">
            <div class="d-flex flex-row justify-content-around">
                @foreach($steps as $step => $controller)
                    <div>
                        <a href="{{ route('install.' . $step) }}"
                           id="install-{{ $step }}-button"
                           class="btn btn-primary btn-circle @if(!$controller::enabled($steps)) disabled @endif"
                           title="@lang("install.$step.title")"
                        >
                            <i class="fa fa-lg {{ $controller::icon() }}"></i>
                        </a>
                    </div>
                @endforeach
            </div>
            <div class="content-divider"></div>
            <div class="row">
                <div id="error-box" class="col-12">
                    @if(!empty($messages))
                        @foreach($messages as $message)
                        <div class="alert alert-danger">{{ $message }}</div>
                        @endforeach
                    @endif
                </div>
            </div>
            @yield('content')
        </div>
    </div>
</div>
<script>
    function checkStepStatus() {
        $.ajax('{{ route('install.action.steps') }}')
        .success(function (data) {
            console.log(data);
            Object.keys(data).forEach(function (key) {
                var button = $('#install-' + key + '-button');
                if (data[key]) {
                    button.removeClass('disabled')
                } else {
                    button.addClass('disabled')
                }
            });
        })
    }
</script>
@yield('scripts')
</body>
</html>
