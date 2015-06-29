$(document).ready(function(){
	
		$("img.lazy").lazyload({
    		effect: "fadeIn",
    		threshold: 300,
		    placeholder: "",
		    skip_invisible: false
    }).removeClass("lazy").removeAttr('width').removeAttr('height');

    $(document).ajaxStop(function() {
        $("img.lazy").lazyload({
            effect: "fadeIn",
    		    threshold: 300,
    		    placeholder: "",
    		    skip_invisible: false
        }).removeClass("lazy").removeAttr('width').removeAttr('height');
    });
});

function wrap_overlib() {
	var ret = overlib.apply(null,arguments);
	$('div#overDiv img').removeAttr('width').removeAttr('height');
	return ret;
}