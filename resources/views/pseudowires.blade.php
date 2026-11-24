@extends('layouts.librenmsv1')

@section('title', __('Pseudowires'))

@section('content')
<div class="container-fluid">
    <x-option-bar name="{{ __('Pseudowires') }}" :options="$options" :selected="$view" />
    @include('pseudowires.table', ['showDevice' => true])
</div>
@endsection
