Leaflet.markercluster
=====================

Provides Marker Clustering functionality for Leaflet

## Using the plugin
See the included example for usage. Or [check it out online here](http://danzel.github.com/Leaflet.markercluster/example/marker-clustering.html)

Create a new MarkerClusterGroup, add your markers to it, then add it to the map

```javascript
var markers = new L.MarkerClusterGroup();
markers.addLayer(new L.Marker(getRandomLatLng(map)));
... Add more layers ...
map.addLayer(markers);
```

For the complete example see example/marker-clustering.html

### Customising the Clustered Marker
As an option to MarkerClusterGroup you can provide your own function for creating the Icon for the clustered markers.
The default implementation changes color at bounds of 10 and 100, but more advanced uses may require customising this.

```javascript
var markers = new L.MarkerClusterGroup({ options: {
	iconCreateFunction: function(childCount) {
		return new L.DivIcon({ html: '<b>' + childCount + '</b>' });
	}
}});
```

### Events

If you register for click, mouseover, etc events on the MarkerClusterGroup you will get callbacks for both individual markers and clusters.
Set your callback up as follows to handle both cases:

```javascript
markers.on('click', function (a) {
	if (a.layer instanceof L.MarkerCluster) {
		console.log('cluster ' + a.layer.getAllChildMarkers().length);
	} else {
		console.log('marker ' + a.layer);
	}
});
```

### License


Leaflet.markercluster is free software, and may be redistributed under the MIT-LICENSE.
