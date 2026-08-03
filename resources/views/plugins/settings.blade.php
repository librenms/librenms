@extends('layouts.librenmsv1')

@section('title', $title)

@section('content')
    {{-- Phase 5 host: .lnms-plugins — plugin.settings include only; no fake controls --}}
    <div class="container-fluid lnms-plugins">
        @include($content_view, $settings)
    </div>
@endsection
