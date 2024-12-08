@extends('device.submenu')

@section('tabcontent')
    @includeFirst(['device.tabs.ports.' . $data['tab'], 'device.tabs.ports.detail'])
@endsection
