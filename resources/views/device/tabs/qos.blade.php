@extends('device.index')

@section('tab')
<x-qos :qosItems="$device->qos->whereNull('port_id')" :show="$data['show']" />
@endsection
