@extends('layouts.librenmsv1')

@section('title', __('TEST Graph'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <x-datepicker :to="$to" :from="$from"></x-datepicker>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
        <!-- TAB NAVIGATION -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="active"><a href="#tab1" role="tab" data-toggle="tab">Dygraph</a></li>
            <li><a href="#tab2" role="tab" data-toggle="tab">Metrics Graphics</a></li>
            <li><a href="#tab3" role="tab" data-toggle="tab">Plotly</a></li>
            <li><a href="#tab4" role="tab" data-toggle="tab">ChartJS</a></li>
        </ul>
        <!-- TAB CONTENT -->
        <div class="tab-content">
            <div class="active tab-pane fade in" id="tab1">
                <div id="dygraph" class="chart-container" style="position: relative; height:40vh; width:80vw"></div>
            </div>
            <div class="tab-pane fade" id="tab2" style="min-height: 520px;">
                <div id="metrics-graphics" class="chart-container" style="position: relative; height:40vh; width:80vw"></div>
            </div>
            <div class="tab-pane fade" id="tab3">
                <div id="plotly" class="chart-container" style="position: relative; height:40vh; width:80vw"></div>
            </div>
            <div class="tab-pane fade" id="tab4">
                <div class="chart-container" style="position: relative; height:40vh; width:80vw">
                    <canvas id="chartjs"></canvas>
                </div>
            </div>
        </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var graphUrl = '{!! $url !!}';

        // ------  ChartJS
        axios.get(graphUrl,{params: {renderer: 'chartjs'}})
            .then(function (response) {
                new Chart(document.getElementById('chartjs').getContext('2d'), response.data);
            }).catch(function (e) {
                console.log(e)
            });

        // ------  Dygraph
        var dygraph;
        var updateDygraph;
        updateDygraph = function (start, end) {
            axios.get(graphUrl, {params: {renderer: 'dygraph', from: start/1000, to: end/1000}})
                .then(function (response) {
                    var config = response.data.config;
                    var data = response.data.data;
                    data.forEach(function (item) {
                        item[0] = new Date(item[0] * 1000)
                    })
                    config['file'] = data;
                    dygraph.updateOptions(config);
                }).catch(function (e) {
                console.log(e)
            });
        };
        axios.get(graphUrl, {params: {renderer: 'dygraph'}})
            .then(function (response) {
                var json = response.data;
                json.data.forEach(function (item) {
                    item[0] = new Date(item[0]*1000)
                })
                json.config['zoomCallback'] = debounce(updateDygraph, 500, false);

                dygraph = new Dygraph(document.getElementById('dygraph'), json.data, json.config);
            }).catch(function (e) {
                console.log(e)
            });

        // ------ Metrics Graphics
        axios.get(graphUrl, {params: {renderer: 'metrics-graphics'}})
            .then(function (response) {
                var config = response.data;
                for (var i = 0; i < config.data.length; i++) {
                    for (var j = 0; j < config.data[i].length; j++) {
                        config.data[i][j][0] = new Date(config.data[i][j][0] * 1000);
                    }
                }
                if (config['yax_format']) {
                    config['yax_format'] = d3.format(config['yax_format']);
                }
                if (config['y_rollover_format']) {
                    config['y_rollover_format'] = d3.format(config['y_rollover_format']);
                }
                config['target'] = document.getElementById('metrics-graphics');
                MG.data_graphic(config);
            }).catch(function (e) {
            console.log(e)
        });

        // ------ Plotly.js
        axios.get(graphUrl, {params: {renderer: 'plotly'}})
            .then(function (response) {
                data = response.data.data;
                for (var i = 0; i < data.length; i++) {
                    for (var j = 0; j < data[i]['x'].length; j++) {
                        data[i]['x'][j] = new Date(data[i]['x'][j] * 1000)
                    }
                }

                Plotly.newPlot( document.getElementById('plotly'), data /*, response.data.layout */);
            }).catch(function (e) {
            console.log(e)
        });


        function unpack(rows, key) {
            return rows.map(function(row) { return row[key]; });
        }

        function debounce(func, wait, immediate) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }
    </script>
@endpush

@section('javascript')
{{--    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>--}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/4.13.0/d3.min.js" integrity="sha512-RJJ1NNC88QhN7dwpCY8rm/6OxI+YdQP48DrLGe/eSAd+n+s1PXwQkkpzzAgoJe4cZFW2GALQoxox61gSY2yQfg==" crossorigin="anonymous"></script>    <script src="https://cdnjs.cloudflare.com/ajax/libs/dygraph/2.1.0/dygraph.min.js" integrity="sha512-opAQpVko4oSCRtt9X4IgpmRkINW9JFIV3An2bZWeFwbsVvDxEkl4TEDiQ2vyhO2TDWfk/lC+0L1dzC5FxKFeJw==" crossorigin="anonymous"></script>
    <script src="https://cdn.plot.ly/plotly-1.58.4.min.js" charset="utf-8"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/metrics-graphics/2.15.6/metricsgraphics.min.js" integrity="sha512-ajcrSc3e0yOZ8tbLioR0G0rrcMvXoJku+UZfOXq2gtwbNLJGhbuzyxo/mAlxHfTegrN51YGvZXT/Gxp7NsIXXw==" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js" integrity="sha256-t9UJPrESBeG2ojKTIcFLPGF7nHi2vEc7f5A2KpH/UBU=" crossorigin="anonymous"></script>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dygraph/2.1.0/dygraph.css" integrity="sha512-QG68tUGWKc1ItPqaThfgSFbubTc+hBv4OW/4W1pGi0HHO5KmijzXzLEOlEbbdfDtVT7t7mOohcOrRC5mxKuaHA==" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/metrics-graphics/2.15.6/metricsgraphics.min.css" integrity="sha512-e5dJblUsk+67Y38sR7+4reR7UyT7BuT/8y0r4ZcXh59IGjAMY4UA81lHouD+a82FU9sGvAsLZlDLgAiUFeKXPg==" crossorigin="anonymous" />
@endpush
