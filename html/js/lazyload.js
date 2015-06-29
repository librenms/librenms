$(document).ready(function(){
	
		$("img.lazy").lazyload({
    		effect: "fadeIn",
    		threshold: 300,
		    placeholder: "",
		    skip_invisible: false
    }).removeClass("lazy");

    $(document).ajaxStop(function() {
        $("img.lazy").lazyload({
            effect: "fadeIn",
    		    threshold: 300,
    		    placeholder: "",
		        skip_invisible: false
        }).removeClass("lazy");
    });
});