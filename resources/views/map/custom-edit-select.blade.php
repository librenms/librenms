@extends('layouts.librenmsv1')

@section('title', __('Select Custom Map To Edit'))

@section('content')
<div class="container-fluid">
  <div class="row" id="control-row">
    <div class="col-md-12">
      <select id="show_group" class="page-availability-report-select" name="show_group" onchange="selectMap(this)">
        <option value="-1" selected>Select map to edit</option>
        <option value="0">Create New Map</option>
@foreach($maps as $map)
        <option value="{{$map->custom_map_id}}">{{$map->name}}</option>
@endforeach
      </select>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    function selectMap(caller) {
        if($(caller).val() < 0) {
            return true;
        } else if ($(caller).val() == 0) {
            window.location.href = "{{ route("maps.custom.edit") }}/new";
        }
        window.location.href = "{{ route("maps.custom.edit") }}/" + $(caller).val();
    }
</script>
@endsection

