$(document).ready(function(){
	
		$("img.lazy").lazyload({
    		effect: "fadeIn",
    		threshold: 300,
		    placeholder: ""
    }).removeClass("lazy").removeAttr('width').removeAttr('height');

    $(document).ajaxStop(function() {
        $("img.lazy").lazyload({
            effect: "fadeIn",
    		    threshold: 300,
    		    placeholder: ""
        }).removeClass("lazy").removeAttr('width').removeAttr('height');
    });
});

function wrap_overlib() {
	var ret = overlib(arguments);
	$('div#overDiv img').removeAttr('width').removeAttr('height');
	return ret;
}