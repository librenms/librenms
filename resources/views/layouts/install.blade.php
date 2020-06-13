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
        body {
            background-color: #046C8B;
        }

        .primary-panel {
            padding: 0;
            border:0;
            box-shadow: 0 0 30px #222;
            min-height: 540px;
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

        .card-img-top {
            background-color: #0794C8;
        }
        #progress-icons {
            background: linear-gradient(to bottom, #0794C8 50%, white 50%)
        }
        #progress-icons .btn {
            background-color: #70A9A1;
            border-color: #66A39B;
        }
        #progress-icons .btn:hover {
            background-color: #548C85;
            border-color: #4D807A;
        }
        #progress-icons .btn.disabled {
            opacity: 1;
            background-color: #A6C9C5;
            border-color: #9AC1BC;
        }

        .install-progress {
            margin-top: auto;
            margin-bottom: auto;
            height: 14px;
            width: 100%;
            background-color: lightgray;
            box-shadow:
                inset 0 6px 4px -5px black,
                inset 0 -6px 4px -7px black;
        }
        .install-progress.complete {
            background-color: yellowgreen;
        }

        #step-title {
            padding-bottom: 20px;
        }
    </style>
    @yield('style')
</head>
<body>
<div class="container">
    <div class="card col-lg-8 offset-lg-2 col-md-10 offset-md-1 col-12 primary-panel">
        <div class="card-img-top">
            <img class="card-img-top p-4" src="{{ asset(\LibreNMS\Config::get('title_image', "images/librenms_logo_dark.svg")) }}" alt="LibreNMS">
            <div id="progress-icons" class="d-flex flex-row justify-content-around">
                <div class="install-progress complete"></div>
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
                    <div class="install-progress"></div>
                @endforeach
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 text-center">
                    <h2 id="step-title">@yield('title')</h2>
                </div>
            </div>
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
