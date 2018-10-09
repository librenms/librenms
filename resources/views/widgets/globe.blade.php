<div id='chart_div'></div>
<script type='text/javascript'>
    google.load('visualization', '1', {'packages': ['geochart'], callback: function() {

            drawRegionsMap();
            function drawRegionsMap() {
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Site');
                data.addColumn('number', 'Status');
                data.addColumn('number', 'Size');
                data.addColumn({type: 'string', role: 'tooltip', 'p': {'html': true}});
                data.addRows({!! $locations !!});
                var options = {
                    region: '{{ $map_world }}',
                    resolution: '{{ $map_countries }}',
                    displayMode: 'markers',
                    keepAspectRatio: 1,
                    magnifyingGlass: {enable: true, zoomFactor: 100},
                    colorAxis: {minValue: 0,  maxValue: 100, colors: ['green', 'yellow', 'red']},
                    markerOpacity: 0.90,
                    tooltip: {isHtml: true}
                };
                var chart = new google.visualization.GeoChart(document.getElementById('chart_div'));
                chart.draw(data, options);
            }
        }
    });
</script>
