@extends('device.index')

@section('tab')
<div class="col-12 col-md-4">
    @isset($data['submenualphabridge'])
        <x-submenualphabridge :title="$title" :menu="$data['submenualphabridge']" :device-id="$device_id" :current-tab="$current_tab" :selected="$vars" />
    @endisset
</div>

<div class="col-12 col-md-8">
    <div class="panel panel-default">
    @yield('tabcontent')
</div>
</div>
@endsection