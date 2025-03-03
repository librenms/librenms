<div id='chart_div-{{ $id }}'></div>

<script type='text/javascript'>
    loadjs('https://www.gstatic.com/charts/loader.js', function() {
        google.charts.load('current', {'packages': ['geochart'], callback: function() {
                var data = new google.visualization.DataTable();
                data.addColumn('number', 'Latitude');
                data.addColumn('number', 'Longitude');
                data.addColumn('string', 'Label');
                data.addColumn('number', 'Status');
                data.addColumn('number', 'Size');
                data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
                data.addRows({!! $locations !!});
                var options = {
                    region: '{{ $region }}',
                    resolution: '{{ $resolution }}',
                    displayMode: 'markers',
                    keepAspectRatio: 1,
                    magnifyingGlass: {enable: true, zoomFactor: 100},
                    colorAxis: {minValue: 0,  maxValue: 100, colors: ['green', 'yellow', 'red']},
                    markerOpacity: 0.90,
                    tooltip: {isHtml: true}
                };
                var chart = new google.visualization.GeoChart(document.getElementById('chart_div-{{ $id }}'));
                chart.draw(data, options);
            }
        });
    });
</script>
