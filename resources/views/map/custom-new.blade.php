@extends('layouts.librenmsv1')

@section('title', __('map.custom.title.create'))

@section('content')
    @include('map.custom-map-modal')
@endsection

@section('scripts')
<script type="text/javascript">
    // Pop up the modal to set initial settings
    $('#mapModal').modal({backdrop: 'static', keyboard: false}, 'show');
    $("#mapBackgroundClearRow").hide();

    function editMapSuccess(data) {
        window.location.href = "{{ @route('maps.custom.edit', ['map' => '?']) }}".replace('?', data['id']);
    }

    function editMapCancel() {
        window.location.href = "{{ route("maps.custom.index") }}";
    }
</script>
@endsection

