@extends('layouts.librenmsv1')

@section('title', __('TEST Graph'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="chart-container" style="position: relative; height:40vh; width:80vw">
                    <canvas id="chart"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var ctx = document.getElementById('chart').getContext('2d');
        var chartData = [];

        axios.get('{{ route('graph_data.port_bits') }}')
            .then(function (response) {
                // JSON responses are automatically parsed.
                chartData = response.data;
                console.log(chartData);
                var myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        datasets: chartData
                    },
                    options: {
                        responsive: true,
                        scales: {
                            xAxes: [{
                                type: 'time',
                                time: {
                                    unit: 'hour',
                                    displayFormats: {hour: 'M-D-YYYY hh:mm', minute: 'DD MM YYYY hh:mm'}
                                },
                                ticks: {
                                    min: moment().subtract(2, 'hour'),
                                    max: moment()
                                }
                            }],
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });
            })
            .catch(function (e) {
                this.errors.push(e)
            })
    </script>
@endsection

@section('javascript')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js" integrity="sha256-t9UJPrESBeG2ojKTIcFLPGF7nHi2vEc7f5A2KpH/UBU=" crossorigin="anonymous"></script>
@endsection
