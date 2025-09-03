@extends('layouts.librenmsv1')

@section('title', __('Edit Alert Rule'))

@section('content')
<div class="row">
    <div class="col-sm-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    {{ __('Edit Alert Rule') }}
                    <div class="pull-right">
                        <a target="_blank" href="https://docs.librenms.org/Alerting/" class="tw:mr-5">
                            <i class="fa fa-book"></i> {{ __('Documentation') }}
                        </a>
                        <a href="javascript:void(0);" onclick="window.history.back();" class="tw:text-gray-700 tw:hover:text-red-600 tw:no-underline tw:text-3xl tw:transition-colors tw:duration-200" title="{{ __('Close') }}">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
                </h3>
            </div>
            <div class="panel-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#main" aria-controls="main" role="tab" data-toggle="tab">{{ __('Main') }}&nbsp;</a></li>
                    <li role="presentation"><a href="#advanced" aria-controls="advanced" role="tab" data-toggle="tab">{{ __('Advanced') }}&nbsp;</a></li>
                </ul>
                <form method="post" role="form" id="rules" class="form-horizontal alerts-form">
                    @csrf
                    <input type="hidden" name="type" id="type" value="alert-rules">
                    <input type="hidden" name="builder_json" id="builder_json" value="">

                    @include('alerts.rules._form', [
                        'mode' => 'edit',
                        'saveUrl' => route('alert-rule.update', $alertRule),
                        'saveMethod' => 'PUT',
                        'loadUrl' => route('alert-rule.show', $alertRule),
                    ])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@include('alerts.rules._modals')

