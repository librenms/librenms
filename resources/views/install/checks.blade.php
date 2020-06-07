@extends('layouts.install')

@section('title', trans('install.checks.title'))

@section('content')
    <div class="row">
        <div class=" col-8 offset-2">
            <div class="checks card ">
                <div class="card-header">
                    <div class="row">
                        <div class="col-8">
                            PHP <small>(@lang('install.checks.php_required', ['version' => $php_required]))</small>
                        </div>
                        <div class="check-status col-4 text-right text-nowrap @if($php_ok) green @else red @endif">
                            {{ $php_version }}
                            @if($php_ok)
                                <i class="fa fa-lg fa-check-square-o green"></i>
                            @else
                                <i class="fa fa-lg fa-times-rectangle-o red"></i>
                            @endif

                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @foreach($modules as $module)
                    <div class="check-row row">
                        <div class="col-8">
                            {{ $module['name'] }}
                        </div>
                        <div class="check-status col-4 text-right">
                            @if($module['status'])
                                <i class="fa fa-lg fa-check-square-o green"></i>
                            @else
                                <i class="fa fa-lg fa-times-rectangle-o red"></i>
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
        .check-status .fa {
            vertical-align: middle;
        }
        .check-status {
            padding-right: 5px;
        }
        .check-row {
            margin-top: -1px;
            padding-top: 11px;
            padding-bottom: 10px;
            border-top: 1px solid #ddd;
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
