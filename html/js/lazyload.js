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