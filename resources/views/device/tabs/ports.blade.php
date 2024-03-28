@extends('device.submenu')

@section('tabcontent')
    @if($data['tab'])
        @includeIf('device.tabs.ports.' . $data['tab'])
    @else
        @include('device.tabs.ports.detail')
    @endif
@endsection
