# jQuery Mapael - Build dynamic vector maps

For more information and documentation, go to [Mapael website](http://neveldo.fr/mapael).

Additional maps are stored in the repository ['neveldo/mapael-maps'](https://github.com/neveldo/mapael-maps).

## Overview

jQuery Mapael is a [jQuery](http://jquery.com/) plugin based on [raphael.js](http://raphaeljs.com/) that allows you to display dynamic vector maps.  

For example, with Mapael, you can display a map of the world with clickable countries. You can build simple dataviz by setting some parameters in order to automatically set a color to each area of your map and generate the legend. Moreover, you can plot cities on a map with their latitude and longitude.

As Raphaël, Mapael supports Firefox 3.0+, Safari 3.0+, Chrome 5.0+, Opera 9.5+ and Internet Explorer 6.0+.

![Dataviz example](http://neveldo.fr/mapael/world-example.png)
[See this example !](http://neveldo.fr/mapael/usecases/world)

## Key features

*   based on **jQuery and raphael.js**
*   **Interactive.** Set a link, a tooltip and some events on the areas of the map
*   **Plottable cities**  with their latitude and their longitude
*   **Areas and plots colorization.** Mapael automatically sets a color to each area of your map and generates an interactive legend in order to build pretty dataviz
*   **Easy to add new maps.** Build your own maps based on SVG format
*   **SEO-friendly.** An alternative content can be set for non-JS users and web crawlers
*   **Resizable** Thanks to raphael.js, maps are easily resizable.
*   **Zoom** Zoom and panning abilities.

## How to use Mapael

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

*   [Minimal example (France)](http://jsfiddle.net/neveldo/tn5AF/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/tn5AF/)
*   [Refreshable map of France with coloured cities and areas labels and zoom buttons](http://jsfiddle.net/neveldo/TKUy4/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/TKUy4/)
*   [Map with some overloaded parameters and 'onclick' callback on areas (France)](http://jsfiddle.net/neveldo/qGwWr/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/qGwWr/)
*   [Population of France by department with a legend](http://jsfiddle.net/neveldo/TUYHN/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/TUYHN/)
*   [Population of the 1000 more populated french cities with a legend](http://jsfiddle.net/neveldo/n6XyQ/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/n6XyQ/)
*   [Map of the world with the population by country](http://jsfiddle.net/neveldo/VqwUZ/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/VqwUZ/)
*   [Map of USA with some plotted cities](http://jsfiddle.net/neveldo/KeBTy/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/KeBTy/)
*   [Open a link in a new tab](http://jsfiddle.net/neveldo/E4hqM/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/E4hqM/)
*   [Setting an image as a plot](http://jsfiddle.net/neveldo/8Ke69/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/8Ke69/)
*   [Playing with the zoom features : focus on specific areas, zoom on scroll](http://jsfiddle.net/neveldo/RahvT/embedded/result/) - [Edit in JSFiddle](http://jsfiddle.net/neveldo/RahvT/)

## License

Copyright (C) 2013 [Vincent Brouté](http://neveldo.fr)

jQuery Mapael is licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php).

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.