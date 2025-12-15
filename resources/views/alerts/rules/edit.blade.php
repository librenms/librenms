@extends('layouts.librenmsv1')

@section('title', __('Edit Alert Rule'))

@section('content')
<div class="row">
    <div class="col-sm-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">

                    @include('alerts.rules._form', [
                        'mode' => 'edit',
                        'saveUrl' => route('alert-rule.update', $alertRule),
                        'saveMethod' => 'PUT',
                        'loadUrl' => route('alert-rule.show', $alertRule),
                    ])

    </div>
</div>
@endsection

@include('alerts.rules._modals')

