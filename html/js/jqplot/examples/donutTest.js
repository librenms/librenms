$(document).ready(function(){

    $.jqplot.config.enablePlugins = true;

    s1 = [['a',2], ['b',8], ['c',14], ['d',20]];
    s2 = [['a', 4], ['b', 12], ['c', 6], ['d', 3]];
    s3 = [['a', 2], ['b', 1], ['c', 3], ['d', 3]];
    s4 = [['a', 4], ['b', 3], ['c', 2], ['d', 1]];
    
    s5 = [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1];
    
    plot1 = $.jqplot('chart1', [s1], {
        seriesDefaults:{
            renderer:$.jqplot.DonutRenderer
        },
        legend: {show:true}
    });
    
    plot2 = $.jqplot('chart2', [s1, s2], {
        seriesDefaults: {
            renderer:$.jqplot.DonutRenderer,
            rendererOptions:{
                sliceMargin: 2,
                innerDiameter: 110,
                startAngle: -90
            }
        }
    });

    plot3 = $.jqplot('chart3', [s1, s2, s3], {
        captureRightClick: true,
        seriesDefaults:{
            renderer:$.jqplot.DonutRenderer,
            shadow: false,
            rendererOptions:{
                innerDiameter: 110,
                startAngle: -90,
                sliceMargin: 2,
                highlightMouseDown: true
            }
        },
        legend: {
            show: true,
            location: 'e',
            placement: 'outside'
        }      
    });

    plot4 = $.jqplot('chart4', [s1, s2, s3, s4], {
        seriesDefaults:{
            renderer:$.jqplot.DonutRenderer
        },
        legend: {
            show: true,
            location: 's',
            placement: 'outside',
            rendererOptions:{
                numberRows: 1
            }
        }
    });

    plot5 = $.jqplot('chart5', [s5], {
        seriesDefaults:{
            renderer:$.jqplot.DonutRenderer
        }
    });
    
    plot6 = $.jqplot('chart6', [[1,2,3,4]]);
    
    $('#chart1').bind('jqplotDataClick', 
        function (ev, seriesIndex, pointIndex, data) {
            $('#info1').html('series: '+seriesIndex+', point: '+pointIndex+', data: '+data);
        }
    );
    
    $('#chart2').bind('jqplotDataHighlight', 
        function (ev, seriesIndex, pointIndex, data) {
            $('#info2').html('series: '+seriesIndex+', point: '+pointIndex+', data: '+data);
        }
    );
    
    $('#chart2').bind('jqplotDataUnhighlight', 
        function (ev) {
            $('#info2').html('Nothing');
        }
    ); 
    
    $('#chart3').bind('jqplotDataRightClick', 
        function (ev, seriesIndex, pointIndex, data) {
            $('#info3').html('series: '+seriesIndex+', point: '+pointIndex+', data: '+data);
        }
    );  
    
    $(document).unload(function() {$('*').unbind(); });
});