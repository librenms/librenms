$(document).ready(function(){
	
		$("img.lazy").lazyload({
    		effect: "fadeIn"
    }).removeClass("lazy");

    $(document).ajaxStop(function() {
        $("img.lazy").lazyload({
            effect: "fadeIn"
        }).removeClass("lazy");
    });

});

function get_overlib(content) {
	var ret = overlib(content, WRAP,HAUTO,VAUTO);
	
	jQuery.event.trigger("ajaxStop");

	return ret;
}