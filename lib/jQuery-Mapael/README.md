# jQuery Mapael - Dynamic vector maps

The complete documentation is available on [Mapael website](http://www.vincentbroute.fr/mapael).

Additional maps are stored in the repository ['neveldo/mapael-maps'](https://github.com/neveldo/mapael-maps).

## Overview

jQuery Mapael is a [jQuery](http://jquery.com/) plugin based on [raphael.js](http://raphaeljs.com/) that allows you to display dynamic vector maps.  

For example, with Mapael, you can display a map of the world with clickable countries. You can also build simple dataviz by setting some parameters in order to automatically set a color depending on a value to each area of your map and display the associated legend. Moreover, you can plot cities on the map with circles, squares or images by their latitude and longitude. Many more options are available, read the documentation in order to get a complete overview of mapael abilities.

As Raphaël, Mapael supports Firefox 3.0+, Safari 3.0+, Chrome 5.0+, Opera 9.5+ and Internet Explorer 6.0+.

![Dataviz example](http://www.vincentbroute.fr/mapael/world-example.png)
[See this example !](http://www.vincentbroute.fr/mapael/usecases/world)

## Key features

*   based on **jQuery and raphael.js**. And optionnaly based on jQuery mousewheel for the zoom on mousewheel feature.
*   **Interactive.** Set href, tooltip, add events and many more on the elements of your map.
*   **Plottable cities**  Cities can be plotted on the map with circles, squares or images by their latitude and longitude
*   **Areas and plotted points colorization with legends.** Mapael automatically sets attributes like color and size to each area and plotted point displayed on map and generates an interactive legend in order to build pretty dataviz
*   **Easy to add new maps.** Build your own maps based on SVG paths
*   **SEO-friendly.** An alternative content can be set for non-JS users and web crawlers
*   **Resizable** Maps are easily resizable.
*   **Zoom** Zoom and panning abilities.

## Basic code example

Here is the simplest example that shows how to display an empty map of the world :

**HTML :**

    <div class="container">
        <div class="map">Alternative content</div>
    </div>

**JS :**

    $(".container").mapael({
        map : {
            name : "world_countries"
        }
    });

## Examples

**Basic**

*   [Minimal example](http://jsfiddle.net/neveldo/tn5AF/)
*   [Map with some custom plotted cities and areas](http://jsfiddle.net/neveldo/z559d0s2/)
*   [Map with zoom-in, zoom-out buttons and zoom on mousewheel](http://jsfiddle.net/neveldo/jh4jzyhw/)
*   [Map with a legend for areas](http://jsfiddle.net/neveldo/TUYHN/)
*   [Map with a legend for plotted cities](http://jsfiddle.net/neveldo/n6XyQ/)
*   [Map with a legend where slices are specified with a fixed value instead of min and max values](http://jsfiddle.net/neveldo/bgjh7a4f/)
*   [Map with a legend for images](http://jsfiddle.net/neveldo/1jjq6g9y/)
*   [Map with a legend for plotted cities and areas](http://jsfiddle.net/neveldo/VqwUZ/)
*   [Use legendSpecificAttrs option to apply specific attributes to the legend elements](http://jsfiddle.net/neveldo/5o16cw7s/)
*   [Map with an horizontal legend for plotted cities and areas](http://jsfiddle.net/neveldo/qr540oyv/)
*   [Map with href on areas and plotted cities](http://jsfiddle.net/neveldo/dqcbkp4z/)

**Advanced**

*   [Map with links between the plotted cities](http://jsfiddle.net/neveldo/yckqj78q/)
*   [Map with multiple plotted cities legends that handle different criteria](http://jsfiddle.net/neveldo/xd2azoxL/)
*   [Trigger an 'update' event for refreshing elements](http://jsfiddle.net/neveldo/TKUy4/)
*   [Use the 'eventHandlers' option and the 'update' event for refreshing areas when the user click on them](http://jsfiddle.net/neveldo/qGwWr/)
*   [Use 'zoom' event in order to zoom on specific areas of the map](http://jsfiddle.net/neveldo/ejf9dsL9/)
*   [Use 'zoom.init' option in order to set an initial zoom level on a specific position](http://jsfiddle.net/neveldo/6ms3vusb/)
*   [Use 'afterInit' option to extend the Raphael paper](http://jsfiddle.net/neveldo/xqpwwLqg/)
*   [Use the 'eventHandlers' option to display information about plotted cities in a div on mouseover](http://jsfiddle.net/neveldo/b5fj4qod/)

## License

Copyright (C) 2013-2015 [Vincent Brouté](http://www.vincentbroute.fr)

jQuery Mapael is licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php).

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
