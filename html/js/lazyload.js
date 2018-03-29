/*
   * LibreNMS module to initialize and support lazy loading of graph images
   *
   * Copyright (c) 2015 Travis Hegner <http://travishegner.com/>
   *
   * This program is free software: you can redistribute it and/or modify it
   * under the terms of the GNU General Public License as published by the
   * Free Software Foundation, either version 3 of the License, or (at your
   * option) any later version.  Please see LICENSE.txt at the top level of
   * the source code distribution for details.
   */
$(document).ready(function(){
    
    //initialize jquery lazyload for all '.lazy' img tags
    $("img.lazy").load(lazyload_done).lazyload({
        effect: "fadeIn",
        threshold: 300,
        placeholder: ""
    });


    //re-initializes images loaded after an ajax call
    $(document).ajaxStop(function() {
        $("img.lazy").load(lazyload_done).lazyload({
            effect: "fadeIn",
            threshold: 300,
            placeholder: ""
        });
    });
});

function lazyload_done() {
    //Since RRD takes the width and height params for only the canvas, we must unset them 
    //from the final (larger) image to prevent the browser from resizing them.
    $(this).removeAttr('height').removeClass('lazy');
}
