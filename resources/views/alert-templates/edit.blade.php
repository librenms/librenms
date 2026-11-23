@extends('layouts.librenmsv1')

@section('title', __('Edit Alert Template'))

@section('content')
    @include('alert-templates.form', ['title' => __('Edit Alert Template') . ': ' . $template->name, 'action' => route('alert-templates.update', $template)])
@endsection
