$(document).ready(function(){
	
		$("img.lazy").lazyload({
    		effect: "fadeIn",
    		threshold: 300,
		    placeholder: ""
    }).removeClass("lazy");

    $(document).ajaxStop(function() {
        $("img.lazy").lazyload({
            effect: "fadeIn",
    		    threshold: 300,
    		    placeholder: ""
        }).removeClass("lazy");
    });
});