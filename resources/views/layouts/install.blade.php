<!DOCTYPE HTML>
<html lang="en">
<head>
    <title>@lang('install.title')</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('css/bootstrap4.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet" type="text/css"/>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/clipboard.min.js') }}"></script>
    <style>
        body {
            background-color: #046C8B;
        }

        .primary-panel {
            padding: 0;
            border: 0;
            box-shadow: 3px 3px 30px #222;
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

        .card-img-top {
            background-color: #EEEEEE;
        }

        #progress-icons {
            background: linear-gradient(to bottom, #EEEEEE 50%, white 50%)
        }

        .btn-circle.disabled {
            opacity: 1;
            background-color: #62bcca;
            border-color: #64c0ce;
        }
        .btn-circle.btn-outline-info:not(:hover) {
            background-color: white;
        }
        .btn-circle.btn-primary-info:not(:hover) {
            background-color: white;
        }

        .install-progress {
            margin: auto -1px auto 0;
            height: 14px;
            display: inline-block;
            width: 100%;
            background-color: lightgray;
            box-shadow: inset 0 6px 4px -5px black,
            inset 0 -6px 4px -7px black;
        }

        .install-progress.loop {
            box-shadow: inset 0 6px 4px -5px black,
            inset 0 -6px 4px -7px black,
            inset 8px 0 4px -6px grey; /* missing button shadow */
        }

        .install-progress.complete {
            background-color: #db202e;
        }

        #step-title {
            padding-bottom: 20px;
        }

        .rotate-if-collapsed {
            transition: .4s transform ease-in-out;
        }

        [data-toggle="collapse"] {
            cursor: pointer;
        }

        [data-toggle="collapse"][aria-expanded="true"] .rotate-if-collapsed {
            transform: rotate(180deg);
        }
    </style>
    @yield('style')
</head>
<body>
<div class="container">
    <div class="card col-lg-8 offset-lg-2 col-md-10 offset-md-1 col-12 primary-panel">
        <div class="card-img-top">
            <img class="card-img-top p-4" src="{{ asset(\LibreNMS\Config::get('title_image', "images/librenms_logo_light.svg")) }}" alt="LibreNMS">
            <div id="progress-icons" class="d-flex flex-row justify-content-around">
                <div class="install-progress complete"></div>
                @foreach($steps as $name => $controller)
                    <div>
                        <a href="{{ route('install.' . $name) }}"
                           id="install-step-{{ $name }}"
                           class="install-step btn btn-circle
                           @if($step === $name) btn-outline-info @else btn-info @endif
                           @if(!$controller->enabled()) disabled @endif"
                           title="@lang("install.$name.title")"
                        >
                            <i class="fa fa-lg {{ $controller->icon() }}"></i>
                        </a>
                    </div>
                    <div id="progress-{{ $name }}-bar" class="install-progress loop @if($controller->complete() || $step == 'finish') complete @endif"></div>
                @endforeach
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 text-center">
                    <h2 id="step-title">@lang("install.$step.title")</h2>
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
    var step = '{{ $step }}';
    function checkStepStatus(callback) {
        $.ajax('{{ route('install.action.steps') }}')
            .done(function (data) {
                var primary;
                Object.keys(data).forEach(function (key) {
                    var classes = 'btn btn-circle';
                    classes += (key === step ? ' btn-outline-info' : ' btn-info');

                    // mark buttons enabled
                    if (!data[key].enabled) {
                        classes += ' disabled';
                    } else if (!data[key].complete && !primary) {
                        // if this step is the first enabled, but not complete, mark it as primary
                        primary = key
                    }

                    $('#install-step-' + key).attr('class', classes);
                });

                if (primary) {
                    $('#install-step-' + primary)
                        .removeClass('btn-info')
                        .removeClass('btn-outline-info')
                        .addClass(primary === step ? 'btn-outline-primary' : 'btn-primary');
                }

                if (callback && typeof callback === "function") {
                    callback(data);
                }
            })
    }

    checkStepStatus();
</script>
@yield('scripts')
</body>
</html>
