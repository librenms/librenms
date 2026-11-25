@extends('layouts.librenmsv1')

@section('title', __('Create Alert Template'))

@section('content')
    @include('alert-templates.form', ['title' => __('Create Alert Template'), 'action' => route('alert-templates.store')])
@endsection
