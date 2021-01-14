@extends('layouts.librenmsv1')

@section('title', __('TEST Graph'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div id="dygraph" class="chart-container" style="position: relative; height:40vh; width:80vw">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="chart-container" style="position: relative; height:40vh; width:80vw">
                    <canvas id="chart"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var ctx = document.getElementById('chart').getContext('2d');
        var div = document.getElementById('dygraph');

        axios.get('{{ route('graph_data.port_bits', ['renderer' => 'chartjs']) }}')
            .then(function (response) {
                console.log(response.data);
                new Chart(ctx, response.data);
            }).catch(function (e) {
                this.errors.push(e)
            })

        axios.get('{{ route('graph_data.port_bits', ['renderer' => 'dygraph']) }}')
            .then(function (response) {
                var json = response.data;
                json.data.forEach(function (item) {
                    item[0] = new Date(item[0]*1000)
                })
                console.log(response.data);
                new Dygraph(div, json.data, json.config);
            }).catch(function (e) {
            this.errors.push(e)
        })
    </script>
@endpush

@section('javascript')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dygraph/2.1.0/dygraph.min.js" integrity="sha512-opAQpVko4oSCRtt9X4IgpmRkINW9JFIV3An2bZWeFwbsVvDxEkl4TEDiQ2vyhO2TDWfk/lC+0L1dzC5FxKFeJw==" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js" integrity="sha256-t9UJPrESBeG2ojKTIcFLPGF7nHi2vEc7f5A2KpH/UBU=" crossorigin="anonymous"></script>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dygraph/2.1.0/dygraph.css" integrity="sha512-QG68tUGWKc1ItPqaThfgSFbubTc+hBv4OW/4W1pGi0HHO5KmijzXzLEOlEbbdfDtVT7t7mOohcOrRC5mxKuaHA==" crossorigin="anonymous" />
@endpush
