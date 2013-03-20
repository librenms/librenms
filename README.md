# Leaflet.awesome-markers plugin
Colorful iconic & retina-proof markers for Leaflet, based on the Font Awesome/Twitter Bootstrap icons

## Screenshots
![AwesomeMarkers screenshot](https://raw.github.com/lvoogdt/Leaflet.awesome-markers/master/screenshots/screenshot-soft.png "Screenshot of AwesomeMarkers")

<a href="http://jsfiddle.net/VPzu4/3/embedded/result/" target="_blank">JSfiddle demo</a> 

### Twitter Bootstrap/Font-Awesome icons
This plugin depends on Bootstrap or Font-Awesome for the rendering of the icons. The Font-Awesome fonts and CSS classes could be included in the project. See these urls for more information:
- http://fortawesome.github.com/Font-Awesome/
- http://fortawesome.github.com/Font-Awesome/#integration

Or if you are using bootstrap:
- http://twitter.github.com/bootstrap/

## Using the plugin
Copy the dist/images directory and css/js files to your project and include them:
````xml
<link rel="stylesheet" href="css/leaflet.awesome-markers.css">
````
````xml
<script src="js/leaflet.awesome-markers.js"></script>
````

Now use the plugin to create a marker like this:
````js
// Creates a red marker with the coffee icon
var redMarker = L.AwesomeMarkers.icon({
  icon: 'coffee', 
  color: 'red'
})

L.marker([51.941196,4.512291], {icon: redMarker}).addTo(map);
````

### Supported colors
The 'color' property currently supports these strings:
- 'red'
- 'darkred'
- 'orange'
- 'green'
- 'darkgreen'
- 'blue'
- 'darkblue'
- 'purple'
- 'darkpurple'
- 'cadetblue'

### Supported icons
The 'icon' property supports these strings:
- 'home'
- 'glass'
- 'flag'
- 'star'
- 'bookmark'
- .... and many more, see: http://fortawesome.github.com/Font-Awesome/#icons-new
- Or: http://twitter.github.com/bootstrap/base-css.html#icons

### Spinning icons (only Font-Awesome)
You can make any icon spin by setting the spin option to true:
````js
// Creates a red marker with the coffee icon
var redMarker = L.AwesomeMarkers.icon({
  icon: 'spinner', 
  color: 'red',
  spin: true
})

L.marker([51.941196,4.512291], {icon: redMarker}).addTo(map);
````

### Color of the icon
By default the icons are white, but you can set the color to black with the iconColor option. 'white' & 'black' are the only ones supported.
````js
// Creates a red marker with the coffee icon
var redMarker = L.AwesomeMarkers.icon({
  icon: 'flag', 
  color: 'red',
  iconColor: 'black'
})

L.marker([51.941196,4.512291], {icon: redMarker}).addTo(map);
````

## License
- Leaflet.AwesomeMarkers and colored markers are licensed under the MIT License - http://opensource.org/licenses/mit-license.html.
- Font Awesome: http://fortawesome.github.com/Font-Awesome/#license
- Twitter Bootstrap: http://twitter.github.com/bootstrap/

## Contact
- Email: lvoogdt@gmail.com
- Website: http://lennardvoogdt.nl
