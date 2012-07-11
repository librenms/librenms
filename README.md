Leaflet.markercluster
=====================

Provides Marker Clustering functionality for Leaflet

##Using the plugin
See the included example for usage.

Create a new MarkerClusterGroup, add your markers to it, then add it to the map

```javascript
var markers = new L.MarkerClusterGroup();
markers.addLayer(new L.Marker(getRandomLatLng(map)));
map.addLayer(markers);
```

For a more complete example see example/marker-clustering.html

###Customising the Clustered Marker
As an option to MarkerClusterGroup you can provide your own function for creating the Icon for the clustered markers.
The default implementation changes color at bounds of 10 and 100, but more advanced uses may require customising this.

```javascript
var markers = new L.MarkerClusterGroup({ options: {
	iconCreateFunction: function(childCount) {
		return new L.DivIcon({ html: '<b>' + childCount + '</b>' });
	}
}});
```