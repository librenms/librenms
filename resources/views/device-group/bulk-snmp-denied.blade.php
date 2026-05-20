@extends('layouts.librenmsv1')

@section('title', __('bulk-snmp.denied.title'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-warning" style="margin-top: 40px;">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-lock"></i> {{ __('bulk-snmp.denied.title') }}
                    </h3>
                </div>
                <div class="panel-body text-center" style="padding: 30px;">
                    <p style="font-size: 16px;">
                        {{ __('bulk-snmp.denied.message') }}
                    </p>
                    <p class="text-muted">
                        {{ __('bulk-snmp.denied.contact') }}
                    </p>
                    <a href="{{ url('/') }}" class="btn btn-default" style="margin-top: 10px;">
                        <i class="fa fa-home"></i> {{ __('bulk-snmp.denied.back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
