@extends('layouts.librenmsv1')

@section('title', __('TEST Graph'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div id="dygraph" class="chart-container" style="position: relative; height:40vh; width:80vw"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12" style="min-height: 520px;">
                <div id="metrics-graphics" class="chart-container" style="position: relative; height:40vh; width:80vw"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="chart-container" style="position: relative; height:40vh; width:80vw">
                    <canvas id="chartjs"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ------  ChartJS
        var ctx = document.getElementById('chartjs').getContext('2d');
        axios.get('{!! $url !!}',{params: {renderer: 'chartjs'}})
            .then(function (response) {
                new Chart(ctx, response.data);
            }).catch(function (e) {
                console.log(e)
            });

        // ------  Dygraph
        var div = document.getElementById('dygraph');
        var dygraph;
        var updateDygraph;
        updateDygraph = function (start, end) {
            axios.get('{!! $url !!}', {params: {renderer: 'dygraph', start: start/1000, end: end/1000}})
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
        axios.get('{!! $url !!}', {params: {renderer: 'dygraph'}})
            .then(function (response) {
                var json = response.data;
                json.data.forEach(function (item) {
                    item[0] = new Date(item[0]*1000)
                })
                json.config['zoomCallback'] = debounce(updateDygraph, 500, false);

                dygraph = new Dygraph(div, json.data, json.config);
            }).catch(function (e) {
                console.log(e)
            });

        // ------ Metrics graphics
        d3.json('{!! $url !!}&renderer=metrics-graphics', function(config) {
            for (var i = 0; i < config.data.length; i++) {
                for (var j = 0; j < config.data[i].length; j++) {
                    config.data[i][j][0] = new Date(config.data[i][j][0] * 1000);
                }
            }
            if (config['yax_format']) {
                config['yax_format'] = d3.format(config['yax_format']);
            }
            config['target'] = document.getElementById('metrics-graphics');
            MG.data_graphic(config);
        });


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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/metrics-graphics/2.15.6/metricsgraphics.min.js" integrity="sha512-ajcrSc3e0yOZ8tbLioR0G0rrcMvXoJku+UZfOXq2gtwbNLJGhbuzyxo/mAlxHfTegrN51YGvZXT/Gxp7NsIXXw==" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js" integrity="sha256-t9UJPrESBeG2ojKTIcFLPGF7nHi2vEc7f5A2KpH/UBU=" crossorigin="anonymous"></script>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dygraph/2.1.0/dygraph.css" integrity="sha512-QG68tUGWKc1ItPqaThfgSFbubTc+hBv4OW/4W1pGi0HHO5KmijzXzLEOlEbbdfDtVT7t7mOohcOrRC5mxKuaHA==" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/metrics-graphics/2.15.6/metricsgraphics.min.css" integrity="sha512-e5dJblUsk+67Y38sR7+4reR7UyT7BuT/8y0r4ZcXh59IGjAMY4UA81lHouD+a82FU9sGvAsLZlDLgAiUFeKXPg==" crossorigin="anonymous" />
@endpush
