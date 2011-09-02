$(document).ready(function(){

    s1 = [['Sony',7], ['Samsumg',13], ['LG',14], ['Vizio',5]];
    s2 = [['a', 4], ['b', 12], ['c', 6], ['d', 3]];
    s3 = [['a', 2], ['b', 1], ['c', 3], ['d', 3]];
    s4 = [['a', 4], ['b', 3], ['c', 2], ['d', 1]];
    
    s5 = [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1];
    
    plot1 = $.jqplot('chart1', [s1], {
        seriesDefaults:{
            renderer:$.jqplot.FunnelRenderer
        }
    }); 
    
    plot2 = $.jqplot('chart2', [s1], {
        seriesDefaults:{
            renderer:$.jqplot.FunnelRenderer,
            rendererOptions: {
                widthRatio: 0.5,
                sectionMargin: 0
            }
        },
        legend: {
            show:true,
            location: 'e'
        }
    });  
    
    plot3 = $.jqplot('chart3', [s1], {
        captureRightClick: true,
        seriesDefaults:{
            renderer:$.jqplot.FunnelRenderer,
            rendererOptions: {
                widthRatio: 0.2,
                sectionMargin: 0,
                highlightMouseDown: true
            }
        },
        legend: {
            show:true,
            location: 'e',
            placement: 'outside'
        }
    });
    
    plot4 = $.jqplot('chart4', [[1,2,3,4]]);
    
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