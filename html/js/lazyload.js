$(document).ready(function(){
	
		$("img.lazy").load(lazyload_done).lazyload({
    		effect: "fadeIn",
    		threshold: 300,
		    placeholder: ""
    });


    $(document).ajaxStop(function() {
        $("img.lazy").load(lazyload_done).lazyload({
            effect: "fadeIn",
    		    threshold: 300,
    		    placeholder: ""
        });
    });
});

function wrap_overlib() {
	var ret = overlib.apply(null,arguments);
	$('div#overDiv img').removeAttr('width').removeAttr('height').removeClass('lazy');
	return ret;
}

function lazyload_done() {
	$(this).removeAttr('width').removeAttr('height').removeClass('lazy');
}