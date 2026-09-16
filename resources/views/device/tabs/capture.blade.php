@extends('layouts.librenmsv1')

@section('content')
<x-device.page :device="$device">
    <h2>{{ __('Capture Debug Information') }}</h2>

    <x-tabs active="discovery">
        @foreach($data['tabs'] as $tab => $tab_data)
            <x-tab :name="$tab_data['name']" :value="$tab">
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-toolbar" role="toolbar" style="margin:5px 0 5px 0">
                            <button type="button" class="btn btn-success" id="run-{{ $tab }}"><i class="fa fa-play fa-lg"></i> {{ __('Run') }}</button>
                            <button type="button" class="btn btn-primary" id="copy-{{ $tab }}"><i class="fa fa-clipboard fa-lg"></i> {{ __('Copy') }}</button>
                            <a class="btn btn-warning" href="{{ str_replace('text', 'download', $tab_data['url']) }}"><i class="fa fa-download fa-lg"></i> {{ __('Download') }}</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <textarea readonly id="output-{{ $tab }}" class="form-control" rows="30" placeholder="{{ __('Output') }}" style="resize:vertical;"></textarea>
                    </div>
                </div>
            </x-tab>
        @endforeach
    </x-tabs>
</x-device.page>
@endsection

@push('scripts')
<script type="text/javascript">
    @foreach($data['tabs'] as $tab => $tab_data)
        document.getElementById('copy-{{ $tab }}').onclick = function() {
            var output = document.getElementById("output-{{ $tab }}");
            output.select();
            try {
                document.execCommand('copy');
            } catch (err) {
                alert('Unsupported Browser!');
            }
        };

        document.getElementById('run-{{ $tab }}').onclick = function () {
            var output = document.getElementById("output-{{ $tab }}");
            var xhr = new XMLHttpRequest();
            xhr.open("GET", @json($tab_data['url']), true);
            xhr.onprogress = function (e) {
                output.innerHTML = e.currentTarget.responseText;
                output.scrollTop = output.scrollHeight - output.clientHeight;
            };
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    console.log("Complete");
                }
            };
            xhr.send();
        };
    @endforeach
</script>
@endpush
