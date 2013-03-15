# Leaflet.AwesomeMarkers plugin
Colorful iconic markers for Leaflet, based on the Font Awesome icons

### Font-Awesome
This plugin depends on Font-Awesome for the rendering of the icons. The Font-Awesome fonts and CSS classes should be included in the project. See these urls for more information:
- http://fortawesome.github.com/Font-Awesome/
- http://fortawesome.github.com/Font-Awesome/#integration

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
  icon: 'icon-coffee', 
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
- 'icon-coffee'
- 'icon-food'
- 'icon-plane'
- 'icon-star'
- 'icon-beer'
- .... and many more, see: http://fortawesome.github.com/Font-Awesome/#icons-new

## License
- Leaflet.AwesomeMarkers and colored markers are licensed under the MIT License - http://opensource.org/licenses/mit-license.html.
- Font Awesome: http://fortawesome.github.com/Font-Awesome/#license

## Contact
- Email: lvoogdt@gmail.com
- Website: http://lennardvoogdt.nl
