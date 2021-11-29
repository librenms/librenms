@extends('layouts.install')

@section('content')
    <div class="row">
        <div class=" col-8 offset-2">
            <div class="checks card ">
                <div class="card-body">
                    <div class="row bg-light check-row">
                        <div class="col-7">
                            PHP <small>({{ __('install.checks.php_required', ['version' => $php_required]) }})</small>
                        </div>
                        <div class="col-5 text-right text-nowrap @if($php_ok) text-success @else text-danger @endif">
                            {{ $php_version }}
                            @if($php_ok)
                                <i class="fa fa-lg fa-check-square-o text-success align-middle"></i>
                            @else
                                <i class="fa fa-lg fa-times-rectangle-o text-danger align-middle"></i>
                            @endif

                        </div>
                    </div>

                    @foreach($modules as $module)
                    <div class="row check-row border-top">
                        <div class="col-7">
                            {{ $module['name'] }}
                        </div>
                        <div class="col-5 text-right">
                            @if($module['status'])
                                <i class="fa fa-lg fa-check-square-o text-success align-middle"></i>
                            @else
                                <i class="fa fa-lg fa-times-rectangle-o text-danger align-middle"></i>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@section('style')
    <style type="text/css">
        .check-row {
            padding-top: 10px;
            padding-bottom: 10px;
            margin-right: -20px;
            margin-left: -20px;
        }
        .checks .card-body {
            padding-top: 0;
            padding-bottom: 0;
        }
        .checks {
            font-size: 16pt;
        }
        small {
            font-size: 10pt;
        }
    </style>
@endsection
