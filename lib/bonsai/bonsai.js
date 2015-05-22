
	down = false;

	var timestamp = new Date().getTime();
	
	var selectedGraph;
	
	zoomGraphAreaOffsetTop			= -20;
	zoomGraphAreaOffsetBottom		= 30;
	zoomGraphAreaOffsetLeft			= 49;
	zoomGraphAreaOffsetRight		= 30;
	
	function pad(number, length) {
            var str = '' + number;
            while (str.length < length) {
                str = '0' + str;
            }
            return str;
	}
	
	function datestr(date){
	 		var curhours 		= date.getHours();
			var curminutes 	= date.getMinutes();
			var curday 			= date.getDate();
			var curmonth 		= date.getMonth()+1;
			var curyear 		= date.getFullYear();
			
			return pad(curday,2)+'/'+pad(curmonth,2)+'/'+curyear +' ' + pad(curhours,2) + ':' + pad(curminutes,2);
	}
	
	function isWithinGraphArea(id,x,y)
	{
		if ((x > $(id).data('zoomGraphImage.left') && x < $(id).data('zoomGraphImage.right')) && (y > $(id).data('zoomGraphImage.top') && y < $(id).data('zoomGraphImage.bottom')))
		{
			return true;	
		}
		return false;	
	}
	
	function initGraph(id,base_url,default_start,default_end){
   
		$(id).data('zoomGraphImage.left',$(id).offset().left + zoomGraphAreaOffsetLeft);
		$(id).data('zoomGraphImage.right',$(id).offset().left + $(id).width() - zoomGraphAreaOffsetRight);
		$(id).data('zoomGraphImage.top',$(id).offset().top + zoomGraphAreaOffsetTop);
		$(id).data('zoomGraphImage.bottom', $(id).offset().top + $(id).height() - zoomGraphAreaOffsetBottom);
		$(id).data('base_url',base_url);
		
		$(id).data('default_start',default_start);
		$(id).data('default_end',default_end);
		
		$(id).data('graph_start',default_start);
		$(id).data('graph_end',default_end);
		$(id).data('defaultpxtosecs',((default_end-default_start)*-1) / ($(id).data('zoomGraphImage.right') - $(id).data('zoomGraphImage.left')));
		//$(id).data('defaultpxtosecs',((default_start-default_end)*-1) / ($(id).data('zoomGraphImage.right') - $(id).data('zoomGraphImage.left')));
		
		$(id).mousemove(function(e){e.preventDefault();});
		
		//attach mousedown event
		$(id).mousedown(function(e){
			
			e.preventDefault();
			if (e.which == 1 && isWithinGraphArea(this,e.pageX,e.pageY))
			{
				$('#zoomBox').css('width',0);
				$('#zoomBox').css('height',0);
				$('#zoomBox').show();
				$('#zoomBox').css('left',e.pageX);
				$('#zoomBox').css('top',e.pageY);
				selectedGraph = $(id);
				
				startx = e.pageX;
				starty = e.pageY; 
				down = true;
			}
			else
			{
				$('#zoomBox').hide();
			}
			return false;
		 });
		 //end mouse down
		 
		 //mousemove event
		 $(id).mousemove(function(e) {
			//console.log($(id).data("graph_end"));
			if (isWithinGraphArea(this,e.pageX,e.pageY)){
				document.body.style.cursor = 'crosshair'
			
				if (down == false)
				{
					currentpos 			= $(id).data('graph_start') - Math.round((parseInt(e.pageX)-$(id).data('zoomGraphImage.left'))*$(id).data('defaultpxtosecs'));
					var date 				= new Date(timestamp +(currentpos));
					
					$('#timepopup').html(datestr(date) + '<br/>Click and drag to zoom');
					$('#timepopup').show();
					$('#timepopup').css('left',e.pageX);
					$('#timepopup').css('top',e.pageY + 20);
				}
			}
			else
			{
				$('#timepopup').hide();
				document.body.style.cursor = 'auto';
			}
		})
		//end mousemove 
	}


	
	jQuery(document).mousemove(function(e){

		if (down == true)
		{
			if (e.pageX < selectedGraph.data('zoomGraphImage.left')){e.pageX = selectedGraph.data('zoomGraphImage.left')}
			if (e.pageX > selectedGraph.data('zoomGraphImage.right')){e.pageX = selectedGraph.data('zoomGraphImage.right')}

			if (e.pageX > startx){
				newwidth 	= e.pageX - startx;
				newposx 	= startx;
			}else{
				newwidth	= startx - e.pageX;
				newposx 	= e.pageX;
			}
			$('#zoomBox').css('left',newposx);
			$('#zoomBox').css('width',newwidth);
			
			$('#zoomBox').css('top',selectedGraph.data('zoomGraphImage.top'));
			$('#zoomBox').css('height',selectedGraph.data('zoomGraphImage.bottom') - selectedGraph.data('zoomGraphImage.top'));
			
			newstart 	= selectedGraph.data('graph_start') - Math.round((parseInt(newposx)-selectedGraph.data('zoomGraphImage.left'))*selectedGraph.data('defaultpxtosecs'));
			newend 		= selectedGraph.data('graph_start') - Math.round((parseInt(newposx)-selectedGraph.data('zoomGraphImage.left')+parseInt(newwidth))*selectedGraph.data('defaultpxtosecs'));
			
			var date = new Date(timestamp +(newstart*1000));
			var curhours = date.getHours();
			var curminutes = date.getMinutes();
			
			var startdate = new Date(timestamp +(newstart*1000));
			var enddate = new Date(timestamp +(newend*1000));
			
			$('#timepopup').html('<strong>From</strong>:' + datestr(startdate) + ' <strong>To</strong>:&nbsp;' + datestr(enddate));
			$('#timepopup').show();
			$('#timepopup').css('left',e.pageX);
			$('#timepopup').css('top',e.pageY + 20);
		
		}
		return false; 
   });
	 
	 
	 jQuery(document).mouseup(function(e){
			down = false

			if ($('#zoomBox').css('display') == 'block' && $('#zoomBox').width() > 1)
			{
				
				newstart 	= selectedGraph.data('graph_start') - Math.round((parseInt($("#zoomBox").offset().left)-selectedGraph.data('zoomGraphImage.left'))*selectedGraph.data('defaultpxtosecs'));
				newend 		= selectedGraph.data('graph_end') - Math.round((parseInt($("#zoomBox").offset().left)-selectedGraph.data('zoomGraphImage.left')-parseInt($("#zoomBox").width()*3))*selectedGraph.data('defaultpxtosecs'));
                                var from = getUrlParameter('from',img_src);
                                var to = getUrlParameter('to',img_src);
				//selectedGraph.attr('src',selectedGraph.data('base_url').replace("from="+from, "from="+newstart).replace("to="+to, "to="+newend));
				selectedGraph.attr('src',selectedGraph.data('base_url') + '&from='+newstart+'&to='+newend);
				selectedGraph.data('graph_start', newstart);
				selectedGraph.data('graph_end',newend);
				selectedGraph.data('defaultpxtosecs', ((newend-newstart)*-1) / (selectedGraph.data('zoomGraphImage.right') - selectedGraph.data('zoomGraphImage.left')))
				timestamp = new Date().getTime();
				$('#zoomBox').hide();
			}
			selectedGraph = null
	 });
	 
	 $('#zoomBox').click(function(e){
		$('#zoomBox').hide();
	 })
	 
	 function reset(id){
			
			id.data('graph_start',id.data('default_start'));
			id.data('graph_end',id.data('default_end'));
	 		id.data('defaultpxtosecs',((id.data('default_start')-id.data('default_end'))*-1) / (id.data('zoomGraphImage.right') - id.data('zoomGraphImage.left')));
			id.attr('src',id.data('base_url') + '&s='+id.data('default_start')+'&e='+id.data('default_end'));
		
		timestamp = new Date().getTime();
	 };
	 

function getUrlParameter(sParam,sPageURL)
{
//  http://stackoverflow.com/questions/19491336/get-url-parameter-jquery
//  http://stackoverflow.com/users/1897010/sameer-kazi
    var tempSplit = sPageURL.split("?");
    var sURLVariables = tempSplit[1].split("&");
    for (var i = 0; i < sURLVariables.length; i++) 
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) 
        {
            return sParameterName[1];
        }
    }
} 
