# jQuery Mouse Wheel Plugin

A jQuery plugin that adds cross-browser mouse wheel support.

In order to use the plugin, simply bind the "mousewheel" event to an element. It also provides two helper methods called `mousewheel` and `unmousewheel` that act just like other event helper methods in jQuery. The event callback receives an extra argument which is the normalized "delta" of the mouse wheel. 

Here is an example of using both the bind and helper method syntax.

    // using bind
    $('#my_elem').bind('mousewheel', function(event, delta) {
        console.log(delta);
    });
    
    // using the event helper
    $('#my_elem').mousewheel(function(event, delta) {
        console.log(delta);
    });


## License

The expandable plugin is licensed under the MIT License (LICENSE.txt).

Copyright (c) 2010 [Brandon Aaron](http://brandonaaron.net)