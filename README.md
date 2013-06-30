# jQuery Mapael

## Overview

jQuery Mapael is a [jQuery](http://jquery.com/) plugin based on [raphael.js](http://raphaeljs.com/) that allows you to display dynamic vector maps.  

For example, with Mapael, you can display a map of the world with clickable countries. You can build simple dataviz by setting some parameters in order to automatically set a color to each area of your map and generate the legend. Moreover, you can plot cities on a map with their latitude and longitude.

As Raphaël, Mapael supports Firefox 3.0+, Safari 3.0+, Chrome 5.0+, Opera 9.5+ and Internet Explorer 6.0+.

## Key features

*   based on **jQuery and raphael.js**
*   **Interactive.** Set a link, a tooltip and some events on the areas of the map
*   **Plottable cities**  with their latitude and their longitude
*   **Areas and plots colorization.** Mapael automatically sets a color to each area of your map and generates the legend in order to build pretty dataviz
*   **Easy to add new maps.** Build your own maps based on SVG format
*   **SEO-friendly.** An alternative content can be set for non-JS users and web crawlers
*   **Resizable** Thanks to raphael.js, maps are easily resizable.

## How to use Mapael

Here is the simplest example that shows how to display an empty map of the world :

**HTML :**

    <div class="container">Alternative content</div>

**JS :**

    $(".container").mapael({
        map : {
            name : "world_countries"
        }
    });

## Examples

*   [Minimal example (France)](http://jsfiddle.net/neveldo/tn5AF/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/tn5AF/)
*   [Map with some plotted cities and area labels (France)](http://jsfiddle.net/neveldo/TKUy4/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/TKUy4/)
*   [Map with some overloaded parameters and 'onclick' callback on areas (France)](http://jsfiddle.net/neveldo/qGwWr/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/qGwWr/)
*   [Population of France by department with a legend](http://jsfiddle.net/neveldo/TUYHN/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/TUYHN/)
*   [Population of the 1000 more populated french cities with a legend](http://jsfiddle.net/neveldo/n6XyQ/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/n6XyQ/)
*   [Map of the world with the population by country](http://jsfiddle.net/neveldo/VqwUZ/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/VqwUZ/)
*   [Map of USA with some plotted cities](http://jsfiddle.net/neveldo/KeBTy/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/KeBTy/)

See all these examples [here](http://www.neveldo.fr/mapael/source/examples.html).

## API reference

All options are provided as an object argument of the function $.fn.mapael(Object options). Some of them have a default value. If you want to redefine these default values, you can overload the variable $.fn.mapael.defaultOptions.

Parameter 'options' : 

*   **map :** main options for the map and default options for all plots and areas
    *   **name :** (String) Name of the map to load
	*   **width :** (Integer) Width of the map. If not specified, the map will get the width of its container.
    *   **tooltip :** (Object) options for the tooltip
        *   **cssClass :**  (String, default value : "mapTooltip") CSS class of the tooltip container.
		*   **css :**  (Object) Additional CSS properties for the tooltip container
    *   **defaultArea :** (Object) Default options for all areas of the map. 
        *   **attrs :** (Object, default value : {fill: "#343434", stroke: "#5d5d5d", stroke-width: 1, stroke-linejoin : "round"}) Default Raphael attributes for all areas. Go to the [Raphael reference](http://raphaeljs.com/reference.html#Element.attr) to view available options.
        *   **attrsHover :** (Object, default value : {fill: "#f38a03", animDuration : 300}) Raphael attributes on mouse hover for all areas. Go to the [Raphael reference](http://raphaeljs.com/reference.html#Element.attr) to view available options. You can set the animation duration with the 'animDuration' option.
        *   **textAttrs :** (Object, default value : {font-size: 15, fill:"#c7c7c7", text-anchor": "center"}) Default Raphael attributes for each text within areas. Go to the [Raphael reference](http://raphaeljs.com/reference.html#Element.attr) to view available options.
        *   **textAttrsHover :** (Object, default value : {fill:"#eaeaea", "animDuration" : 300}) Default Raphael attributes on mouse hover for each text within areas. Go to the [Raphael reference](http://raphaeljs.com/reference.html#Element.attr) to view available options. You can set the animation duration with the 'animDuration' option.
        *   **text :** (String) Label displayed within the area
        *   **tooltip :** (Object) Options for the tooltip
            *   **content :** (String) Tooltip content to display on mouse hover
        *   **onclick :** (function(params, mapElem, textElem)) function called on 'onclick' event
        *   **onmouseenter :** (function(params, mapElem, textElem)) function called on 'onmouseenter' event
        *   **onmouseleave :** (function(params, mapElem, textElem)) function called on 'onmouseleave' event
    *   **defaultPlot :** (Object) Default options for all plots of the map.
        *   **type :** (String, default value : "circle") Plot shape : 'circle' or 'square'.
        *   **size :** (Integer, default : 15) The default size of all plots.
        *   **attrs :** (Object, default value : {fill: "#0088db", stroke: "#fff", stroke-width: 0, stroke-linejoin : "round"}) Default Raphael attributes for all plots. Go to the [Raphael reference](http://raphaeljs.com/reference.html#Element.attr) to view available options.
        *   **attrsHover :** (Object, default value : {stroke-width: 3, animDuration : 300}) Raphael attributes on mouse hover for all plots. Go to the [Raphael reference](http://raphaeljs.com/reference.html#Element.attr) to view available options. You can set the animation duration with the 'animDuration' option.
        *   **textAttrs :** (Object, default value : {font-size: 15, fill:"#c7c7c7", text-anchor": "start"}) Default Raphael attributes for each text next to the plots. Go to the [Raphael reference](http://raphaeljs.com/reference.html#Element.attr) to view available options.
        *   **textAttrsHover :** (Object, default value : {fill:"#eaeaea", "animDuration" : 300}) Default Raphael attributes on mouse hover for each text next to the plots. Go to the [Raphael reference](http://raphaeljs.com/reference.html#Element.attr) to view available options. You can set the animation duration with the 'animDuration' option.
        *   **text :** (String) Label displayed near the plot
        *   **tooltip :** (Object) Options for the tooltip
            *   **content :** (String) Tooltip content to display on mouse hover
        *   **onclick :** (function(params, mapElem, textElem)) function called on 'onclick' event
        *   **onmouseover :** (function(params, mapElem, textElem)) function called on 'onmouseover' event
        *   **onmouseout :** (function(params, mapElem, textElem)) function called on 'onmouseout' event
*   **legend :** (Object) Legend options. Define how to display the legend and how to display plots and areas depending on their associated values.
    *   **area :** (Object). Options for the areas legend.
        *   **cssClass :** (String, default value : "mapLegend") CSS class of the container for the areas legend.
        *   **display :** (Boolean, default value : false) Display the legend.
        *   **marginLeft :** (Integer, default value : 15) Margin left for each line of the legend.
        *   **marginLeftTitle :** (Integer, default value : 5) Margin left for title of the legend.
        *   **marginLeftLabel :** (Integer, default value : 10) Margin left for the label of each slice.
        *   **marginBottom :** (Integer, default value : 15) Margin bottom under each line of the legend.
        *   **titleAttrs : ** (Object, default value : {"font-size" : 18, fill : "#343434", "text-anchor" : "start"}) Raphael attributes for the title of the legend. Go to the [Raphael reference](http://raphaeljs.com/reference.html#Element.attr) to view available options.
        *   **labelAttrs : ** (Object, default value : {"font-size" : 15, fill : "#343434", "text-anchor" : "start"}) Raphael attributes for the labels of each slice. Go to the [Raphael reference](http://raphaeljs.com/reference.html#Element.attr) to view available options.
        *   **slices :**(Array, default : []) Array of slice options. For each slice, options are provided as an object :
            *   **min :** (Float) The minimal value of the slice
            *   **max :** (Float) The maximal value of the slice
            *   **label :** (String) The label of the slice for the legend
            *   **+ all options from map.defaultArea can be overloaded here**
    *   **plot :** (Object). Options for the plots legend.
        *   **cssClass :** (String, default value : "mapLegend") CSS class of the container for the areas legend.
        *   **display :** (Boolean, default value : false) Display the legend.
        *   **marginLeft :** (Integer, default value : 15) Margin left for each line of the legend.
        *   **marginLeftTitle :** (Integer, default value : 5) Margin left for title of the legend.
        *   **marginLeftLabel :** (Integer, default value : 10) Margin left for the label of each slice.
        *   **marginBottom :** (Integer, default value : 15) Margin bottom under each line of the legend.
        *   **titleAttrs : ** (Object, default value : {"font-size" : 18, fill : "#343434", "text-anchor" : "start"}) Raphael attributes for the title of the legend. Go to the [Raphael reference](http://raphaeljs.com/reference.html#Element.attr) to view available options.
        *   **labelAttrs : ** (Object, default value : {"font-size" : 15, fill : "#343434", "text-anchor" : "start"}) Raphael attributes for the labels of each slice. Go to the [Raphael reference](http://raphaeljs.com/reference.html#Element.attr) to view available options.
        *   **slices :** (Array, default : []) Array of options for each slice. For each slice, options are provided as an object :
            *   **min :** (Float) The minimal value of the slice
            *   **max :** (Float) The maximal value of the slice
            *   **label :** (String) The label of the slice for the legend
            *   **+ all options from map.defaultPlot can be overloaded here**
*   **areas :** (Object, default : []) List of specific options for each area to display on the map. Areas must be identified according to the ids from the JS file of the intended map. For each area, options are provided as an object :
    *   **value :** (Float) Value associated with the area in order to get the proper options from the legend.
    *   **+ all options from map.defaultArea can be overloaded here** 
*   **plots :** (Array, default : []) Array of specific options for each plot to display on the map. A plot can be positioned with a (latitude, longitude) or a (x, y) couple. For each plot, options are provided as an object :
    *   **value :** (Float) Value associated with the plot in order to get the proper options from the legend.
    *   **latitude :** (Float) latitude of the plot
    *   **longitude :** (Float) longitude of the plot
    *   **x :** (Float) X coordinate of the plot
    *   **y :** (Float) Y coordinate of the plot
    *   **+ all options from map.defaultPlot can be overloaded here** 

## How to add new maps ?

Maps for the world, France and USA countries are available with Mapael. It's easy to create new maps, so feel free to add new ones. 
The first step is to retrieve the SVG file of the wanted map. You can find this kind of resources on [Natural Earth Data](http://www.naturalearthdata.com) or [Wikimedia Commons](http://commons.wikimedia.org/wiki/Category:SVG_maps). Then, you have to create a new JS file from this template :

    (function($) {
        $.extend(true, $.fn.mapael, 
            {
                maps :{
                    yourMapName : {
                        width : 600,
                        height : 500,
                        getCoords : function (lat, lon) {
                            // Convert latitude,longitude to x,y here
                            return {x : 1, y : 1};
                        }
                        elems : {
                            // List of SVG paths for building the map
                        }
                    }
                }
            }
        );
    })(jQuery);

You have to set the default width and height of your map. If you want to plot cities, you will have to customize the getCoords() function that takes as arguments a latitude and a longitude, and returns x,y coordinates depending on the map projection (mercator, miller, ...).
Then, the last step is to open the SVG image with a text editor and copy the paths definitions into the "elems" parameter.
In order to use your new map, you need to load the JS file, and set 'yourMapName' for the Mapael 'name' parameter.

## License

Copyright (C) 2013 [Vincent Brouté](http://neveldo.fr)

jQuery Mapael is licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php).

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.