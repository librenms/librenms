@extends('layouts.install')

@section('content')
    <div class="row">
        <div class=" col-8 offset-2">
            <div class="checks card ">
                <div class="card-body">
                    @foreach($versions as $version)
                        <div class="row check-row @if($loop->first) bg-light @else border-top @endif">
                            <div class="col-7">
                                {{ $version['name'] }} <small>({{ __('install.checks.php_required', ['version' => $version['required']]) }})</small>
                            </div>
                            <div class="col-5 text-right text-nowrap @if($version['ok']) text-success @else text-danger @endif">
                                {{ $version['version'] ?: __('install.checks.not_found') }}
                                @if($version['ok'])
                                    <i class="fa-solid fa-lg fa-square-check text-success align-middle"></i>
                                @else
                                    <i class="fa-solid fa-lg fa-rectangle-xmark text-danger align-middle"></i>
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
