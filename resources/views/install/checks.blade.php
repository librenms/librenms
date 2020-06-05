@extends('layouts.install')

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <h4 class="text-center">@lang('install.checks.title')</h4>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <table class="table table-condensed table-bordered">
                <tr>
                    <th class="col-xs-3">@lang('install.checks.item')</th>
                    <th class="col-xs-2">@lang('install.checks.status')</th>
                    <th class="col-xs-7">@lang('install.checks.comment')</th>
                </tr>
                @foreach($checks as $check)
                <tr class="{{ $check['status'] ? 'success' : 'danger' }}">
                    <td>{{ $check['item'] }}</td>
                    <td>@if($check['status'])
                            <i class="fa fa-check-circle alert-success"></i>
                        @else
                            <i class="fa fa-times-circle alert-danger"></i>
                        @endif</td>
                    <td>{{ $check['comment'] ?? '' }}</td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
